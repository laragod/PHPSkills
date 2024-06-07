<?php

declare(strict_types=1);

namespace Laragod\Skills\Elo;

use Laragod\Skills\GameInfo;

/**
 * Including Elo's scheme as a simple comparison.
 * See http://en.wikipedia.org/wiki/Elo_rating_system#Theory
 * for more details
 */
class FideEloCalculator extends TwoPlayerEloCalculator
{
    public function __construct(FideKFactor $kFactor)
    {
        parent::__construct($kFactor);
    }

    public static function createWithDefaultKFactor(): FideEloCalculator
    {
        return new FideEloCalculator(new FideKFactor());
    }

    public static function createWithProvisionalKFactor(): FideEloCalculator
    {
        return new FideEloCalculator(new ProvisionalFideKFactor());
    }

    public function getPlayerWinProbability(GameInfo $gameInfo, $playerRating, $opponentRating): float
    {
        $ratingDifference = $opponentRating - $playerRating;

        return 1.0
        /
        (
            1.0 + pow(10.0, $ratingDifference / (2 * $gameInfo->getBeta()))
        );
    }
}
