<?php

declare(strict_types=1);

namespace Laragod\Skills;

/**
 * Parameters about the game used when calculating TrueSkill ratings.
 *
 * The defaults follow the original TrueSkill paper: an initial mean of 25, an
 * initial standard deviation of 25/3, a beta of 25/6, a dynamics factor of
 * 25/300 and a 10% draw probability.
 */
final readonly class GameInfo
{
    public const DEFAULT_INITIAL_MEAN = 25.0;

    public const DEFAULT_INITIAL_STANDARD_DEVIATION = 25.0 / 3.0;

    /** Default initial mean / 6. */
    public const DEFAULT_BETA = 25.0 / 6.0;

    /** Default initial mean / 300. */
    public const DEFAULT_DYNAMICS_FACTOR = 25.0 / 300.0;

    public const DEFAULT_DRAW_PROBABILITY = 0.10;

    /**
     * @param  float  $initialMean  The mean (mu) a new player starts with.
     * @param  float  $initialStandardDeviation  The standard deviation (sigma) a new player starts with.
     * @param  float  $beta  The distance in skill points that guarantees roughly an 80% chance of winning.
     * @param  float  $dynamicsFactor  The additive dynamics factor (tau) applied before every game.
     * @param  float  $drawProbability  The probability of a draw, between 0.0 and 1.0.
     */
    public function __construct(
        private float $initialMean = self::DEFAULT_INITIAL_MEAN,
        private float $initialStandardDeviation = self::DEFAULT_INITIAL_STANDARD_DEVIATION,
        private float $beta = self::DEFAULT_BETA,
        private float $dynamicsFactor = self::DEFAULT_DYNAMICS_FACTOR,
        private float $drawProbability = self::DEFAULT_DRAW_PROBABILITY,
    ) {
        Guard::argumentInRangeInclusive($drawProbability, 0.0, 1.0, 'drawProbability');
    }

    public function getInitialMean(): float
    {
        return $this->initialMean;
    }

    public function getInitialStandardDeviation(): float
    {
        return $this->initialStandardDeviation;
    }

    public function getBeta(): float
    {
        return $this->beta;
    }

    public function getDynamicsFactor(): float
    {
        return $this->dynamicsFactor;
    }

    public function getDrawProbability(): float
    {
        return $this->drawProbability;
    }

    /**
     * The rating every new player starts with.
     */
    public function getDefaultRating(): Rating
    {
        return new Rating($this->initialMean, $this->initialStandardDeviation);
    }
}
