<?php

declare(strict_types=1);

namespace Laragod\Skills\TrueSkill;

use Laragod\Skills\Numerics\GaussianDistribution;

final class DrawMargin
{
    /**
     * Derived from the TrueSkill technical report (MSR-TR-2006-80), page 6:
     *
     *     draw probability = 2 * CDF(margin / (sqrt(n1 + n2) * beta)) - 1
     *
     * which implies
     *
     *     margin = inverseCDF((draw probability + 1) / 2) * sqrt(n1 + n2) * beta
     *
     * where n1 and n2 are the number of players on each team (1 and 1 here).
     */
    public static function getDrawMarginFromDrawProbability(float $drawProbability, float $beta): float
    {
        return GaussianDistribution::inverseCumulativeTo(0.5 * ($drawProbability + 1), 0.0, 1.0) * sqrt(1 + 1) * $beta;
    }
}
