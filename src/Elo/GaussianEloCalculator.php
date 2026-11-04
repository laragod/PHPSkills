<?php

declare(strict_types=1);

namespace Laragod\Skills\Elo;

use Laragod\Skills\GameInfo;
use Laragod\Skills\Numerics\GaussianDistribution;

/**
 * Elo with a Gaussian (instead of logistic) win probability, as described in the TrueSkill paper.
 */
final class GaussianEloCalculator extends TwoPlayerEloCalculator
{
    /** From the TrueSkill paper. */
    public const STABLE_KFACTOR = 24.0;

    public function __construct()
    {
        parent::__construct(new KFactor(self::STABLE_KFACTOR));
    }

    public function getPlayerWinProbability(GameInfo $gameInfo, float $playerRating, float $opponentRating): float
    {
        $ratingDifference = $playerRating - $opponentRating;

        // See equation 1.1 in the TrueSkill paper
        return GaussianDistribution::cumulativeTo($ratingDifference / (sqrt(2) * $gameInfo->getBeta()));
    }
}
