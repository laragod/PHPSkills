<?php

declare(strict_types=1);

namespace Laragod\Skills\Elo;

use Laragod\Skills\GameInfo;
use Laragod\Skills\PairwiseComparison;
use Laragod\Skills\PlayersRange;
use Laragod\Skills\RankSorter;
use Laragod\Skills\RatingContainer;
use Laragod\Skills\SkillCalculator;
use Laragod\Skills\SkillCalculatorSupportedOptions;
use Laragod\Skills\TeamsRange;

/**
 * Base class for Elo calculators: two teams of exactly one player each.
 */
abstract class TwoPlayerEloCalculator extends SkillCalculator
{
    protected function __construct(
        protected readonly KFactor $kFactor,
    ) {
        parent::__construct(SkillCalculatorSupportedOptions::NONE, TeamsRange::exactly(2), PlayersRange::exactly(1));
    }

    public function calculateNewRatings(GameInfo $gameInfo, array $teams, array $teamRanks): RatingContainer
    {
        $this->validateTeamCountAndPlayersCountPerTeam($teams);
        $this->validateTeamRanks($teams, $teamRanks);

        RankSorter::sort($teams, $teamRanks);

        $isDraw = $teamRanks[0] === $teamRanks[1];

        $player1 = $teams[0]->getAllPlayers()[0];
        $player2 = $teams[1]->getAllPlayers()[0];

        $player1Rating = $teams[0]->getRating($player1)->getMean();
        $player2Rating = $teams[1]->getRating($player2)->getMean();

        $result = new RatingContainer;
        $result->setRating($player1, $this->calculateNewRating($gameInfo, $player1Rating, $player2Rating, $isDraw ? PairwiseComparison::DRAW : PairwiseComparison::WIN));
        $result->setRating($player2, $this->calculateNewRating($gameInfo, $player2Rating, $player1Rating, $isDraw ? PairwiseComparison::DRAW : PairwiseComparison::LOSE));

        return $result;
    }

    protected function calculateNewRating(GameInfo $gameInfo, float $selfRating, float $opponentRating, PairwiseComparison $selfToOpponentComparison): EloRating
    {
        $expectedProbability = $this->getPlayerWinProbability($gameInfo, $selfRating, $opponentRating);
        $actualProbability = $this->getScoreFromComparison($selfToOpponentComparison);
        $k = $this->kFactor->getValueForRating($selfRating);
        $ratingChange = $k * ($actualProbability - $expectedProbability);

        return new EloRating($selfRating + $ratingChange);
    }

    private function getScoreFromComparison(PairwiseComparison $comparison): float
    {
        return match ($comparison) {
            PairwiseComparison::WIN => 1.0,
            PairwiseComparison::DRAW => 0.5,
            PairwiseComparison::LOSE => 0.0,
        };
    }

    /**
     * The probability (0.0 to 1.0) that a player with the given rating beats the opponent.
     */
    abstract public function getPlayerWinProbability(GameInfo $gameInfo, float $playerRating, float $opponentRating): float;

    public function calculateMatchQuality(GameInfo $gameInfo, array $teams): float
    {
        $this->validateTeamCountAndPlayersCountPerTeam($teams);

        $player1Rating = $teams[0]->getAllRatings()[0]->getMean();
        $player2Rating = $teams[1]->getAllRatings()[0]->getMean();

        // The TrueSkill paper mentions that they used s1 - s2 (rating difference) to
        // determine match quality. We convert that to a percentage as a delta from 50%
        // using the cumulative density function of the specific curve being used.
        $deltaFrom50Percent = abs($this->getPlayerWinProbability($gameInfo, $player1Rating, $player2Rating) - 0.5);

        return (0.5 - $deltaFrom50Percent) / 0.5;
    }
}
