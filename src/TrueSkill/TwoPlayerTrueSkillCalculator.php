<?php

declare(strict_types=1);

namespace Laragod\Skills\TrueSkill;

use Laragod\Skills\GameInfo;
use Laragod\Skills\Numerics\BasicMath;
use Laragod\Skills\PairwiseComparison;
use Laragod\Skills\PlayersRange;
use Laragod\Skills\RankSorter;
use Laragod\Skills\Rating;
use Laragod\Skills\RatingContainer;
use Laragod\Skills\SkillCalculator;
use Laragod\Skills\SkillCalculatorSupportedOptions;
use Laragod\Skills\TeamsRange;

/**
 * Calculates the new ratings for exactly two players.
 *
 * When you only have two players, a lot of the math simplifies. The main purpose of this class
 * is to show the bare minimum of what a TrueSkill implementation should have.
 */
final class TwoPlayerTrueSkillCalculator extends SkillCalculator
{
    public function __construct()
    {
        parent::__construct(SkillCalculatorSupportedOptions::NONE, TeamsRange::exactly(2), PlayersRange::exactly(1));
    }

    public function calculateNewRatings(GameInfo $gameInfo, array $teams, array $teamRanks): RatingContainer
    {
        $this->validateTeamCountAndPlayersCountPerTeam($teams);
        $this->validateTeamRanks($teams, $teamRanks);

        // Make sure things are in order
        RankSorter::sort($teams, $teamRanks);

        // Since we verified that each team has one player, we know the player is the first one
        $winner = $teams[0]->getAllPlayers()[0];
        $winnerPreviousRating = $teams[0]->getRating($winner);

        $loser = $teams[1]->getAllPlayers()[0];
        $loserPreviousRating = $teams[1]->getRating($loser);

        $wasDraw = $teamRanks[0] === $teamRanks[1];

        $results = new RatingContainer;

        $results->setRating($winner, $this->calculateNewRating(
            $gameInfo,
            $winnerPreviousRating,
            $loserPreviousRating,
            $wasDraw ? PairwiseComparison::DRAW : PairwiseComparison::WIN
        ));

        $results->setRating($loser, $this->calculateNewRating(
            $gameInfo,
            $loserPreviousRating,
            $winnerPreviousRating,
            $wasDraw ? PairwiseComparison::DRAW : PairwiseComparison::LOSE
        ));

        return $results;
    }

    private function calculateNewRating(GameInfo $gameInfo, Rating $selfRating, Rating $opponentRating, PairwiseComparison $comparison): Rating
    {
        $drawMargin = DrawMargin::getDrawMarginFromDrawProbability($gameInfo->getDrawProbability(), $gameInfo->getBeta());

        $c = sqrt(
            BasicMath::square($selfRating->getStandardDeviation())
            + BasicMath::square($opponentRating->getStandardDeviation())
            + 2 * BasicMath::square($gameInfo->getBeta())
        );

        $winningMean = $selfRating->getMean();
        $losingMean = $opponentRating->getMean();

        if ($comparison === PairwiseComparison::LOSE) {
            $winningMean = $opponentRating->getMean();
            $losingMean = $selfRating->getMean();
        }

        $meanDelta = $winningMean - $losingMean;

        if ($comparison === PairwiseComparison::DRAW) {
            $v = TruncatedGaussianCorrectionFunctions::vWithinMarginScaled($meanDelta, $drawMargin, $c);
            $w = TruncatedGaussianCorrectionFunctions::wWithinMarginScaled($meanDelta, $drawMargin, $c);
            $rankMultiplier = 1;
        } else {
            $v = TruncatedGaussianCorrectionFunctions::vExceedsMarginScaled($meanDelta, $drawMargin, $c);
            $w = TruncatedGaussianCorrectionFunctions::wExceedsMarginScaled($meanDelta, $drawMargin, $c);
            $rankMultiplier = $comparison->value;
        }

        $varianceWithDynamics = BasicMath::square($selfRating->getStandardDeviation()) + BasicMath::square($gameInfo->getDynamicsFactor());

        $meanMultiplier = $varianceWithDynamics / $c;
        $stdDevMultiplier = $varianceWithDynamics / BasicMath::square($c);

        $newMean = $selfRating->getMean() + ($rankMultiplier * $meanMultiplier * $v);
        $newStdDev = sqrt($varianceWithDynamics * (1 - $w * $stdDevMultiplier));

        return new Rating($newMean, $newStdDev);
    }

    public function calculateMatchQuality(GameInfo $gameInfo, array $teams): float
    {
        $this->validateTeamCountAndPlayersCountPerTeam($teams);

        $player1Rating = $teams[0]->getAllRatings()[0];
        $player2Rating = $teams[1]->getAllRatings()[0];

        // We just use equation 4.1 found on page 8 of the TrueSkill 2006 paper:
        $betaSquared = BasicMath::square($gameInfo->getBeta());
        $player1SigmaSquared = BasicMath::square($player1Rating->getStandardDeviation());
        $player2SigmaSquared = BasicMath::square($player2Rating->getStandardDeviation());

        // This is the square root part of the equation:
        $sqrtPart = sqrt(
            (2 * $betaSquared)
            / (2 * $betaSquared + $player1SigmaSquared + $player2SigmaSquared)
        );

        // This is the exponent part of the equation:
        $expPart = exp(
            (-1 * BasicMath::square($player1Rating->getMean() - $player2Rating->getMean()))
            / (2 * (2 * $betaSquared + $player1SigmaSquared + $player2SigmaSquared))
        );

        return $sqrtPart * $expPart;
    }
}
