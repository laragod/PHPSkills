<?php

declare(strict_types=1);

namespace Laragod\Skills\Elo;

use Laragod\Skills\GameInfo;

/**
 * The classic FIDE (chess) Elo scheme, included as a simple comparison to TrueSkill.
 *
 * @see https://en.wikipedia.org/wiki/Elo_rating_system#Theory
 */
final class FideEloCalculator extends TwoPlayerEloCalculator
{
    public function __construct(FideKFactor $kFactor = new FideKFactor)
    {
        parent::__construct($kFactor);
    }

    public static function createWithDefaultKFactor(): self
    {
        return new self(new FideKFactor);
    }

    public static function createWithProvisionalKFactor(): self
    {
        return new self(new ProvisionalFideKFactor);
    }

    public function getPlayerWinProbability(GameInfo $gameInfo, float $playerRating, float $opponentRating): float
    {
        $ratingDifference = $opponentRating - $playerRating;

        return 1.0 / (1.0 + 10.0 ** ($ratingDifference / (2 * $gameInfo->getBeta())));
    }
}
