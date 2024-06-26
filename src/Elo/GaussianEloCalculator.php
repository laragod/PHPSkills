<?php

declare(strict_types=1);

namespace Laragod\Skills\Elo;

use Laragod\Skills\GameInfo;
use Laragod\Skills\Numerics\GaussianDistribution;

class GaussianEloCalculator extends TwoPlayerEloCalculator
{
    // From the paper
    const STABLE_KFACTOR = 24;

    public function __construct()
    {
        parent::__construct(new KFactor(self::STABLE_KFACTOR));
    }

    public function getPlayerWinProbability(GameInfo $gameInfo, $playerRating, $opponentRating): float
    {
        $ratingDifference = $playerRating - $opponentRating;

        // See equation 1.1 in the TrueSkill paper
        return GaussianDistribution::cumulativeTo(
            $ratingDifference
            /
            (sqrt(2) * $gameInfo->getBeta())
        );
    }
}
