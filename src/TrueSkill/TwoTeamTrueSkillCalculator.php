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
use Laragod\Skills\Team;
use Laragod\Skills\TeamsRange;

/**
 * Calculates new ratings for exactly two teams where each team has one or more players.
 *
 * When you only have two teams, the math is still simple: no factor graphs are used yet.
 */
final class TwoTeamTrueSkillCalculator extends SkillCalculator
{
    public function __construct()
    {
        parent::__construct(SkillCalculatorSupportedOptions::NONE, TeamsRange::exactly(2), PlayersRange::atLeast(1));
    }

    public function calculateNewRatings(GameInfo $gameInfo, array $teams, array $teamRanks): RatingContainer
    {
        $this->validateTeamCountAndPlayersCountPerTeam($teams);
        $this->validateTeamRanks($teams, $teamRanks);

        RankSorter::sort($teams, $teamRanks);

        $team1 = $teams[0];
        $team2 = $teams[1];

        $wasDraw = $teamRanks[0] === $teamRanks[1];

        $results = new RatingContainer;

        $this->updatePlayerRatings($gameInfo, $results, $team1, $team2, $wasDraw ? PairwiseComparison::DRAW : PairwiseComparison::WIN);
        $this->updatePlayerRatings($gameInfo, $results, $team2, $team1, $wasDraw ? PairwiseComparison::DRAW : PairwiseComparison::LOSE);

        return $results;
    }

    private function updatePlayerRatings(
        GameInfo $gameInfo,
        RatingContainer $newPlayerRatings,
        Team $selfTeam,
        Team $otherTeam,
        PairwiseComparison $selfToOtherTeamComparison,
    ): void {
        $drawMargin = DrawMargin::getDrawMarginFromDrawProbability($gameInfo->getDrawProbability(), $gameInfo->getBeta());

        $betaSquared = BasicMath::square($gameInfo->getBeta());
        $tauSquared = BasicMath::square($gameInfo->getDynamicsFactor());

        $totalPlayers = $selfTeam->count() + $otherTeam->count();

        $meanGetter = static fn (Rating $rating): float => $rating->getMean();
        $varianceGetter = static fn (Rating $rating): float => BasicMath::square($rating->getStandardDeviation());

        $selfMeanSum = BasicMath::sum($selfTeam->getAllRatings(), $meanGetter);
        $otherTeamMeanSum = BasicMath::sum($otherTeam->getAllRatings(), $meanGetter);

        $c = sqrt(
            BasicMath::sum($selfTeam->getAllRatings(), $varianceGetter)
            + BasicMath::sum($otherTeam->getAllRatings(), $varianceGetter)
            + $totalPlayers * $betaSquared
        );

        $winningMean = $selfMeanSum;
        $losingMean = $otherTeamMeanSum;

        if ($selfToOtherTeamComparison === PairwiseComparison::LOSE) {
            $winningMean = $otherTeamMeanSum;
            $losingMean = $selfMeanSum;
        }

        $meanDelta = $winningMean - $losingMean;

        if ($selfToOtherTeamComparison === PairwiseComparison::DRAW) {
            $v = TruncatedGaussianCorrectionFunctions::vWithinMarginScaled($meanDelta, $drawMargin, $c);
            $w = TruncatedGaussianCorrectionFunctions::wWithinMarginScaled($meanDelta, $drawMargin, $c);
            $rankMultiplier = 1;
        } else {
            $v = TruncatedGaussianCorrectionFunctions::vExceedsMarginScaled($meanDelta, $drawMargin, $c);
            $w = TruncatedGaussianCorrectionFunctions::wExceedsMarginScaled($meanDelta, $drawMargin, $c);
            $rankMultiplier = $selfToOtherTeamComparison->value;
        }

        foreach ($selfTeam as $player => $previousPlayerRating) {
            $varianceWithDynamics = BasicMath::square($previousPlayerRating->getStandardDeviation()) + $tauSquared;

            $meanMultiplier = $varianceWithDynamics / $c;
            $stdDevMultiplier = $varianceWithDynamics / BasicMath::square($c);

            $newMean = $previousPlayerRating->getMean() + ($rankMultiplier * $meanMultiplier * $v);
            $newStdDev = sqrt($varianceWithDynamics * (1 - $w * $stdDevMultiplier));

            $newPlayerRatings->setRating($player, new Rating($newMean, $newStdDev));
        }
    }

    public function calculateMatchQuality(GameInfo $gameInfo, array $teams): float
    {
        $this->validateTeamCountAndPlayersCountPerTeam($teams);

        // We've verified that there's just two teams
        $team1Ratings = $teams[0]->getAllRatings();
        $team2Ratings = $teams[1]->getAllRatings();

        $totalPlayers = count($team1Ratings) + count($team2Ratings);

        $betaSquared = BasicMath::square($gameInfo->getBeta());

        $meanGetter = static fn (Rating $rating): float => $rating->getMean();
        $varianceGetter = static fn (Rating $rating): float => BasicMath::square($rating->getStandardDeviation());

        $team1MeanSum = BasicMath::sum($team1Ratings, $meanGetter);
        $team1StdDevSquared = BasicMath::sum($team1Ratings, $varianceGetter);

        $team2MeanSum = BasicMath::sum($team2Ratings, $meanGetter);
        $team2StdDevSquared = BasicMath::sum($team2Ratings, $varianceGetter);

        // This comes from equation 4.1 in the TrueSkill paper on page 8
        // The equation was broken up into the part under the square root sign and
        // the exponential part to make the code easier to read.
        $sqrtPart = sqrt(
            ($totalPlayers * $betaSquared)
            / ($totalPlayers * $betaSquared + $team1StdDevSquared + $team2StdDevSquared)
        );

        $expPart = exp(
            (-1 * BasicMath::square($team1MeanSum - $team2MeanSum))
            / (2 * ($totalPlayers * $betaSquared + $team1StdDevSquared + $team2StdDevSquared))
        );

        return $expPart * $sqrtPart;
    }
}
