<?php

declare(strict_types=1);

namespace Laragod\Skills\TrueSkill;

use Laragod\Skills\Numerics\GaussianDistribution;

class TruncatedGaussianCorrectionFunctions
{
    // These functions from the bottom of page 4 of the TrueSkill paper.

    /**
     * The "V" function where the team performance difference is greater than the draw margin.
     *
     * In the reference F# implementation, this is referred to as "the additive
     * correction of a single-sided truncated Gaussian with unit variance."
     *
     * @param  number  $drawMargin  In the paper, it's referred to as just "ε".
     * @return float
     */
    public static function vExceedsMarginScaled($teamPerformanceDifference, $drawMargin, $c) : float|int
    {
        return self::vExceedsMargin($teamPerformanceDifference / $c, $drawMargin / $c);
    }

    public static function vExceedsMargin($teamPerformanceDifference, $drawMargin) : float|int
    {
        $denominator = GaussianDistribution::cumulativeTo($teamPerformanceDifference - $drawMargin);

        if ($denominator < 2.222758749e-162) {
            return -$teamPerformanceDifference + $drawMargin;
        }

        return GaussianDistribution::at($teamPerformanceDifference - $drawMargin) / $denominator;
    }

    /**
     * The "W" function where the team performance difference is greater than the draw margin.
     *
     * In the reference F# implementation, this is referred to as "the multiplicative
     * correction of a single-sided truncated Gaussian with unit variance."
     *
     * @return float
     */
    public static function wExceedsMarginScaled($teamPerformanceDifference, $drawMargin, $c) : float|int
    {
        return self::wExceedsMargin($teamPerformanceDifference / $c, $drawMargin / $c);
    }

    public static function wExceedsMargin($teamPerformanceDifference, $drawMargin) : float|int
    {
        $denominator = GaussianDistribution::cumulativeTo($teamPerformanceDifference - $drawMargin);

        if ($denominator < 2.222758749e-162) {
            if ($teamPerformanceDifference < 0.0) {
                return 1.0;
            }

            return 0.0;
        }

        $vWin = self::vExceedsMargin($teamPerformanceDifference, $drawMargin);

        return $vWin * ($vWin + $teamPerformanceDifference - $drawMargin);
    }

    // the additive correction of a double-sided truncated Gaussian with unit variance
    public static function vWithinMarginScaled($teamPerformanceDifference, $drawMargin, $c) : float|int
    {
        return self::vWithinMargin($teamPerformanceDifference / $c, $drawMargin / $c);
    }

    // from F#:
    public static function vWithinMargin($teamPerformanceDifference, $drawMargin) : float|int
    {
        $teamPerformanceDifferenceAbsoluteValue = abs($teamPerformanceDifference);
        $denominator =
            GaussianDistribution::cumulativeTo($drawMargin - $teamPerformanceDifferenceAbsoluteValue) -
            GaussianDistribution::cumulativeTo(-$drawMargin - $teamPerformanceDifferenceAbsoluteValue);

        if ($denominator < 2.222758749e-162) {
            if ($teamPerformanceDifference < 0.0) {
                return -$teamPerformanceDifference - $drawMargin;
            }

            return -$teamPerformanceDifference + $drawMargin;
        }

        $numerator = GaussianDistribution::at(-$drawMargin - $teamPerformanceDifferenceAbsoluteValue) -
            GaussianDistribution::at($drawMargin - $teamPerformanceDifferenceAbsoluteValue);

        if ($teamPerformanceDifference < 0.0) {
            return -$numerator / $denominator;
        }

        return $numerator / $denominator;
    }

    // the multiplicative correction of a double-sided truncated Gaussian with unit variance
    public static function wWithinMarginScaled($teamPerformanceDifference, $drawMargin, $c) : float|int
    {
        return self::wWithinMargin($teamPerformanceDifference / $c, $drawMargin / $c);
    }

    // From F#:
    public static function wWithinMargin($teamPerformanceDifference, $drawMargin) : float|int
    {
        $teamPerformanceDifferenceAbsoluteValue = abs($teamPerformanceDifference);
        $denominator = GaussianDistribution::cumulativeTo($drawMargin - $teamPerformanceDifferenceAbsoluteValue)
            -
            GaussianDistribution::cumulativeTo(-$drawMargin - $teamPerformanceDifferenceAbsoluteValue);

        if ($denominator < 2.222758749e-162) {
            return 1.0;
        }

        $vt = self::vWithinMargin($teamPerformanceDifferenceAbsoluteValue, $drawMargin);

        return $vt * $vt +
        (
            ($drawMargin - $teamPerformanceDifferenceAbsoluteValue)
            *
            GaussianDistribution::at(
                $drawMargin - $teamPerformanceDifferenceAbsoluteValue)
            - (-$drawMargin - $teamPerformanceDifferenceAbsoluteValue)
            *
            GaussianDistribution::at(-$drawMargin - $teamPerformanceDifferenceAbsoluteValue)) / $denominator;
    }
}
