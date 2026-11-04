<?php

declare(strict_types=1);

namespace Laragod\Skills\Elo;

/**
 * The FIDE K-factor for established players.
 *
 * @see https://ratings.fide.com/calculator_rtd.phtml
 */
class FideKFactor extends KFactor
{
    public function getValueForRating(float $rating): float
    {
        return $rating < 2400 ? 15.0 : 10.0;
    }
}
