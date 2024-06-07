<?php

declare(strict_types=1);

namespace Laragod\Skills\TrueSkill;

use Laragod\Skills\Numerics\GaussianDistribution;

final class DrawMargin
{
    public static function getDrawMarginFromDrawProbability($drawProbability, $beta) : float|int
    {
        // Derived from TrueSkill technical report (MSR-TR-2006-80), page 6

        // draw probability = 2 * CDF(margin/(sqrt(n1+n2)*beta)) -1

        // implies
        //
        // margin = inversecdf((draw probability + 1)/2) * sqrt(n1+n2) * beta
        // n1 and n2 are the number of players on each team
        return GaussianDistribution::inverseCumulativeTo(.5 * ($drawProbability + 1), 0, 1) * sqrt(1 + 1) * $beta;
    }
}
