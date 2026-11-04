<?php

declare(strict_types=1);

namespace Laragod\Skills;

use Laragod\Skills\Numerics\GaussianDistribution;
use Stringable;

/**
 * Container for a player's rating.
 */
class Rating implements Stringable
{
    public const CONSERVATIVE_STANDARD_DEVIATION_MULTIPLIER = 3.0;

    /**
     * @param  float  $mean  The statistical mean value of the rating (also known as mu).
     * @param  float  $standardDeviation  The standard deviation of the rating (also known as sigma).
     * @param  float  $conservativeStandardDeviationMultiplier  The number of standard deviations to subtract from the mean to get a conservative rating.
     */
    public function __construct(
        private readonly float $mean,
        private readonly float $standardDeviation,
        private readonly float $conservativeStandardDeviationMultiplier = self::CONSERVATIVE_STANDARD_DEVIATION_MULTIPLIER,
    ) {}

    /**
     * The statistical mean value of the rating (also known as mu).
     */
    public function getMean(): float
    {
        return $this->mean;
    }

    /**
     * The standard deviation (the spread) of the rating (also known as sigma).
     */
    public function getStandardDeviation(): float
    {
        return $this->standardDeviation;
    }

    /**
     * A conservative estimate of skill based on the mean and standard deviation.
     */
    public function getConservativeRating(): float
    {
        return $this->mean - $this->conservativeStandardDeviationMultiplier * $this->standardDeviation;
    }

    /**
     * Linearly interpolates between a prior and a full posterior rating in the canonical
     * (precision / precision-mean) space, as clarified by Ralf Herbrich.
     *
     * @param  float  $updatePercentage  0.0 keeps the prior, 1.0 applies the full posterior.
     */
    public static function getPartialUpdate(Rating $prior, Rating $fullPosterior, float $updatePercentage): Rating
    {
        $priorGaussian = new GaussianDistribution($prior->getMean(), $prior->getStandardDeviation());
        $posteriorGaussian = new GaussianDistribution($fullPosterior->getMean(), $fullPosterior->getStandardDeviation());

        $precisionDifference = $posteriorGaussian->getPrecision() - $priorGaussian->getPrecision();
        $partialPrecisionDifference = $updatePercentage * $precisionDifference;

        $precisionMeanDifference = $posteriorGaussian->getPrecisionMean() - $priorGaussian->getPrecisionMean();
        $partialPrecisionMeanDifference = $updatePercentage * $precisionMeanDifference;

        $partialPosteriorGaussian = GaussianDistribution::fromPrecisionMean(
            $priorGaussian->getPrecisionMean() + $partialPrecisionMeanDifference,
            $priorGaussian->getPrecision() + $partialPrecisionDifference
        );

        return new Rating(
            $partialPosteriorGaussian->getMean(),
            $partialPosteriorGaussian->getStandardDeviation(),
            $prior->conservativeStandardDeviationMultiplier
        );
    }

    public function __toString(): string
    {
        return sprintf('mean=%.4f, standardDeviation=%.4f', $this->mean, $this->standardDeviation);
    }
}
