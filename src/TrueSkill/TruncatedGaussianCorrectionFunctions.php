<?php

declare(strict_types=1);

namespace Laragod\Skills\TrueSkill;

use Laragod\Skills\Numerics\GaussianDistribution;

/**
 * The V and W functions from the bottom of page 4 of the TrueSkill paper.
 */
final class TruncatedGaussianCorrectionFunctions
{
    /**
     * Below this value the denominators are treated as zero to avoid numeric blow-ups.
     */
    private const SMALLEST_DENOMINATOR = 2.222758749e-162;

    /**
     * The "V" function where the team performance difference is greater than the draw margin.
     *
     * In the reference F# implementation, this is referred to as "the additive
     * correction of a single-sided truncated Gaussian with unit variance."
     *
     * @param  float  $drawMargin  In the paper this is referred to as epsilon.
     */
    public static function vExceedsMarginScaled(float $teamPerformanceDifference, float $drawMargin, float $c): float
    {
        return self::vExceedsMargin($teamPerformanceDifference / $c, $drawMargin / $c);
    }

    public static function vExceedsMargin(float $teamPerformanceDifference, float $drawMargin): float
    {
        $denominator = GaussianDistribution::cumulativeTo($teamPerformanceDifference - $drawMargin);

        if ($denominator < self::SMALLEST_DENOMINATOR) {
            return -$teamPerformanceDifference + $drawMargin;
        }

        return GaussianDistribution::at($teamPerformanceDifference - $drawMargin) / $denominator;
    }

    /**
     * The "W" function where the team performance difference is greater than the draw margin.
     *
     * In the reference F# implementation, this is referred to as "the multiplicative
     * correction of a single-sided truncated Gaussian with unit variance."
     */
    public static function wExceedsMarginScaled(float $teamPerformanceDifference, float $drawMargin, float $c): float
    {
        return self::wExceedsMargin($teamPerformanceDifference / $c, $drawMargin / $c);
    }

    public static function wExceedsMargin(float $teamPerformanceDifference, float $drawMargin): float
    {
        $denominator = GaussianDistribution::cumulativeTo($teamPerformanceDifference - $drawMargin);

        if ($denominator < self::SMALLEST_DENOMINATOR) {
            return $teamPerformanceDifference < 0.0 ? 1.0 : 0.0;
        }

        $vWin = self::vExceedsMargin($teamPerformanceDifference, $drawMargin);

        return $vWin * ($vWin + $teamPerformanceDifference - $drawMargin);
    }

    /**
     * The additive correction of a double-sided truncated Gaussian with unit variance.
     */
    public static function vWithinMarginScaled(float $teamPerformanceDifference, float $drawMargin, float $c): float
    {
        return self::vWithinMargin($teamPerformanceDifference / $c, $drawMargin / $c);
    }

    public static function vWithinMargin(float $teamPerformanceDifference, float $drawMargin): float
    {
        $teamPerformanceDifferenceAbsoluteValue = abs($teamPerformanceDifference);
        $denominator =
            GaussianDistribution::cumulativeTo($drawMargin - $teamPerformanceDifferenceAbsoluteValue) -
            GaussianDistribution::cumulativeTo(-$drawMargin - $teamPerformanceDifferenceAbsoluteValue);

        if ($denominator < self::SMALLEST_DENOMINATOR) {
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

    /**
     * The multiplicative correction of a double-sided truncated Gaussian with unit variance.
     */
    public static function wWithinMarginScaled(float $teamPerformanceDifference, float $drawMargin, float $c): float
    {
        return self::wWithinMargin($teamPerformanceDifference / $c, $drawMargin / $c);
    }

    public static function wWithinMargin(float $teamPerformanceDifference, float $drawMargin): float
    {
        $teamPerformanceDifferenceAbsoluteValue = abs($teamPerformanceDifference);
        $denominator =
            GaussianDistribution::cumulativeTo($drawMargin - $teamPerformanceDifferenceAbsoluteValue) -
            GaussianDistribution::cumulativeTo(-$drawMargin - $teamPerformanceDifferenceAbsoluteValue);

        if ($denominator < self::SMALLEST_DENOMINATOR) {
            return 1.0;
        }

        $vt = self::vWithinMargin($teamPerformanceDifferenceAbsoluteValue, $drawMargin);

        return $vt * $vt +
            (
                ($drawMargin - $teamPerformanceDifferenceAbsoluteValue)
                * GaussianDistribution::at($drawMargin - $teamPerformanceDifferenceAbsoluteValue)
                - (-$drawMargin - $teamPerformanceDifferenceAbsoluteValue)
                * GaussianDistribution::at(-$drawMargin - $teamPerformanceDifferenceAbsoluteValue)
            ) / $denominator;
    }
}
