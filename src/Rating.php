<?php

declare(strict_types=1);

namespace Laragod\Skills;

// Container for a player's rating.
use Laragod\Skills\Numerics\GaussianDistribution;

class Rating
{
    const CONSERVATIVE_STANDARD_DEVIATION_MULTIPLIER = 3;

    private $_conservativeStandardDeviationMultiplier;

    private $_mean;

    private $_standardDeviation;

    /**
     * Constructs a rating.
     *
     * @param  float  $mean  The statistical mean value of the rating (also known as mu).
     * @param  float  $standardDeviation  The standard deviation of the rating (also known as s).
     * @param  float|int  $conservativeStandardDeviationMultiplier  optional The number of standardDeviations to subtract from the mean to achieve a conservative rating.
     */
    public function __construct($mean, $standardDeviation, $conservativeStandardDeviationMultiplier = self::CONSERVATIVE_STANDARD_DEVIATION_MULTIPLIER)
    {
        $this->_mean = $mean;
        $this->_standardDeviation = $standardDeviation;
        $this->_conservativeStandardDeviationMultiplier = $conservativeStandardDeviationMultiplier;
    }

    /**
     * The statistical mean value of the rating (also known as �).
     */
    public function getMean(): float
    {
        return $this->_mean;
    }

    /**
     * The standard deviation (the spread) of the rating. This is also known as s.
     */
    public function getStandardDeviation(): float
    {
        return $this->_standardDeviation;
    }

    /**
     * A conservative estimate of skill based on the mean and standard deviation.
     */
    public function getConservativeRating()
    {
        return $this->_mean - $this->_conservativeStandardDeviationMultiplier * $this->_standardDeviation;
    }

    public function getPartialUpdate(Rating $prior, Rating $fullPosterior, $updatePercentage): Rating
    {
        $priorGaussian = new GaussianDistribution($prior->getMean(), $prior->getStandardDeviation());
        $posteriorGaussian = new GaussianDistribution($fullPosterior->getMean(), $fullPosterior->getStandardDeviation());

        // From a clarification email from Ralf Herbrich:
        // "the idea is to compute a linear interpolation between the prior and posterior skills of each player
        //  ... in the canonical space of parameters"

        $precisionDifference = $posteriorGaussian->getPrecision() - $priorGaussian->getPrecision();
        $partialPrecisionDifference = $updatePercentage * $precisionDifference;

        $precisionMeanDifference = $posteriorGaussian->getPrecisionMean() - $priorGaussian->getPrecisionMean();
        $partialPrecisionMeanDifference = $updatePercentage * $precisionMeanDifference;

        $partialPosteriorGaussion = GaussianDistribution::fromPrecisionMean(
            $priorGaussian->getPrecisionMean() + $partialPrecisionMeanDifference,
            $priorGaussian->getPrecision() + $partialPrecisionDifference
        );

        return new Rating($partialPosteriorGaussion->getMean(), $partialPosteriorGaussion->getStandardDeviation(), $prior->_conservativeStandardDeviationMultiplier);
    }

    public function __toString()
    {
        return sprintf('mean=%.4f, standardDeviation=%.4f', $this->_mean, $this->_standardDeviation);
    }
}
