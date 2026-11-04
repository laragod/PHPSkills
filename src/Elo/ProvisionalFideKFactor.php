<?php

declare(strict_types=1);

namespace Laragod\Skills\Elo;

/**
 * The FIDE K-factor for provisional players (fewer than 30 games).
 */
final class ProvisionalFideKFactor extends FideKFactor
{
    public function getValueForRating(float $rating): float
    {
        return 25.0;
    }
}
