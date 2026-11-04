<?php

declare(strict_types=1);

namespace Laragod\Skills\TrueSkill;

use Closure;
use Laragod\Skills\GameInfo;
use Laragod\Skills\Numerics\BasicMath;
use Laragod\Skills\Numerics\DiagonalMatrix;
use Laragod\Skills\Numerics\Matrix;
use Laragod\Skills\Numerics\Vector;
use Laragod\Skills\PartialPlay;
use Laragod\Skills\PlayersRange;
use Laragod\Skills\RankSorter;
use Laragod\Skills\Rating;
use Laragod\Skills\RatingContainer;
use Laragod\Skills\SkillCalculator;
use Laragod\Skills\SkillCalculatorSupportedOptions;
use Laragod\Skills\Team;
use Laragod\Skills\TeamsRange;

/**
 * Calculates TrueSkill using a full factor graph: any number of teams, any number of
 * players per team, with support for partial play and partial updates.
 */
final class FactorGraphTrueSkillCalculator extends SkillCalculator
{
    public function __construct()
    {
        parent::__construct(
            SkillCalculatorSupportedOptions::PARTIAL_PLAY | SkillCalculatorSupportedOptions::PARTIAL_UPDATE,
            TeamsRange::atLeast(2),
            PlayersRange::atLeast(1)
        );
    }

    public function calculateNewRatings(GameInfo $gameInfo, array $teams, array $teamRanks): RatingContainer
    {
        $this->validateTeamCountAndPlayersCountPerTeam($teams);
        $this->validateTeamRanks($teams, $teamRanks);

        RankSorter::sort($teams, $teamRanks);

        $factorGraph = new TrueSkillFactorGraph($gameInfo, $teams, $teamRanks);
        $factorGraph->buildGraph();
        $factorGraph->runSchedule();

        // Like the reference implementation, the marginals are rebuilt from the final
        // messages while computing the normalization constant.
        $factorGraph->getProbabilityOfRanking();

        return $factorGraph->getUpdatedRatings();
    }

    public function calculateMatchQuality(GameInfo $gameInfo, array $teams): float
    {
        $this->validateTeamCountAndPlayersCountPerTeam($teams);

        // We need to create the A matrix which is the player team assignments.
        $skillsMatrix = $this->getPlayerCovarianceMatrix($teams);
        $meanVector = $this->getPlayerMeansVector($teams);
        $meanVectorTranspose = $meanVector->getTranspose();

        $playerTeamAssignmentsMatrix = $this->createPlayerTeamAssignmentMatrix($teams, $meanVector->getRowCount());
        $playerTeamAssignmentsMatrixTranspose = $playerTeamAssignmentsMatrix->getTranspose();

        $betaSquared = BasicMath::square($gameInfo->getBeta());

        $start = Matrix::multiply($meanVectorTranspose, $playerTeamAssignmentsMatrix);

        $aTa = Matrix::multiply(
            Matrix::scalarMultiply($betaSquared, $playerTeamAssignmentsMatrixTranspose),
            $playerTeamAssignmentsMatrix
        );

        $aTSA = Matrix::multiply(
            Matrix::multiply($playerTeamAssignmentsMatrixTranspose, $skillsMatrix),
            $playerTeamAssignmentsMatrix
        );

        $middle = Matrix::add($aTa, $aTSA);

        $middleInverse = $middle->getInverse();

        $end = Matrix::multiply($playerTeamAssignmentsMatrixTranspose, $meanVector);

        $expPartMatrix = Matrix::scalarMultiply(-0.5, Matrix::multiply(Matrix::multiply($start, $middleInverse), $end));
        $expPart = $expPartMatrix->getDeterminant();

        $sqrtPartNumerator = $aTa->getDeterminant();
        $sqrtPartDenominator = $middle->getDeterminant();
        $sqrtPart = $sqrtPartNumerator / $sqrtPartDenominator;

        return exp($expPart) * sqrt($sqrtPart);
    }

    /**
     * A simple vector of all the player means.
     *
     * @param  list<Team>  $teams
     */
    private function getPlayerMeansVector(array $teams): Vector
    {
        return new Vector($this->getPlayerRatingValues($teams, static fn (Rating $rating): float => $rating->getMean()));
    }

    /**
     * A square matrix whose diagonal values represent the variance (square of standard
     * deviation) of all players.
     *
     * @param  list<Team>  $teams
     */
    private function getPlayerCovarianceMatrix(array $teams): DiagonalMatrix
    {
        return new DiagonalMatrix(
            $this->getPlayerRatingValues($teams, static fn (Rating $rating): float => BasicMath::square($rating->getStandardDeviation()))
        );
    }

    /**
     * Helper function that gets a list of values for all player ratings.
     *
     * @param  list<Team>  $teams
     * @param  Closure(Rating): float  $playerRatingFunction
     * @return list<float>
     */
    private function getPlayerRatingValues(array $teams, Closure $playerRatingFunction): array
    {
        $playerRatingValues = [];

        foreach ($teams as $currentTeam) {
            foreach ($currentTeam->getAllRatings() as $currentRating) {
                $playerRatingValues[] = $playerRatingFunction($currentRating);
            }
        }

        return $playerRatingValues;
    }

    /**
     * The team assignment matrix is often referred to as the "A" matrix. It's a matrix whose rows represent the players
     * and the columns represent teams. At Matrix[row, column] represents that player[row] is on team[col]
     * Positive values represent an assignment and a negative value means that we subtract the value of the next
     * team since we're dealing with pairs. This means that this matrix always has teams - 1 columns.
     * The only other tricky thing is that values represent the play percentage.
     *
     * For example, consider a 3 team game where team1 is just player1, team 2 is player 2 and player 3, and
     * team3 is just player 4. Furthermore, player 2 and player 3 on team 2 played 25% and 75% of the time
     * (e.g. partial play), the A matrix would be:
     *
     *     A = this 4x2 matrix:
     *     |  1.00  0.00 |
     *     | -0.25  0.25 |
     *     | -0.75  0.75 |
     *     |  0.00 -1.00 |
     *
     * @param  list<Team>  $teams
     */
    private function createPlayerTeamAssignmentMatrix(array $teams, int $totalPlayers): Matrix
    {
        $playerAssignments = [];
        $totalPreviousPlayers = 0;

        $teamCount = count($teams);

        for ($currentColumn = 0; $currentColumn < $teamCount - 1; $currentColumn++) {
            $currentTeam = $teams[$currentColumn];

            // Need to add in 0's for all the previous players, since they're not on this team
            $playerAssignments[$currentColumn] = $totalPreviousPlayers > 0 ? array_fill(0, $totalPreviousPlayers, 0.0) : [];

            foreach ($currentTeam->getAllPlayers() as $currentPlayer) {
                $playerAssignments[$currentColumn][] = PartialPlay::getPartialPlayPercentage($currentPlayer);
                // indicates the player is on the team
                $totalPreviousPlayers++;
            }

            $rowsRemaining = $totalPlayers - $totalPreviousPlayers;

            $nextTeam = $teams[$currentColumn + 1];

            foreach ($nextTeam->getAllPlayers() as $nextTeamPlayer) {
                // Add a -1 * playing time to represent the difference
                $playerAssignments[$currentColumn][] = -1 * PartialPlay::getPartialPlayPercentage($nextTeamPlayer);
                $rowsRemaining--;
            }

            for ($ixAdditionalRow = 0; $ixAdditionalRow < $rowsRemaining; $ixAdditionalRow++) {
                // Pad with zeros
                $playerAssignments[$currentColumn][] = 0.0;
            }
        }

        return Matrix::fromColumnValues($totalPlayers, $teamCount - 1, $playerAssignments);
    }
}
