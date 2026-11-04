<?php

declare(strict_types=1);

namespace Laragod\Skills\Tests;

use Laragod\Skills\Rating;

final class RatingTest extends TestCase
{
    public function test_conservative_rating_subtracts_three_standard_deviations_by_default(): void
    {
        $rating = new Rating(25.0, 25.0 / 3.0);

        $this->assertEqualsWithDelta(0.0, $rating->getConservativeRating(), 0.000001);
        $this->assertEqualsWithDelta(8.3333, (new Rating(25.0, 25.0 / 3.0, 2.0))->getConservativeRating(), 0.0001);
    }

    public function test_partial_update_interpolates_between_prior_and_posterior(): void
    {
        $prior = new Rating(25.0, 25.0 / 3.0);
        $posterior = new Rating(29.39583201999924, 7.171475587326186);

        $none = Rating::getPartialUpdate($prior, $posterior, 0.0);
        $this->assertEqualsWithDelta($prior->getMean(), $none->getMean(), 0.000001);
        $this->assertEqualsWithDelta($prior->getStandardDeviation(), $none->getStandardDeviation(), 0.000001);

        $full = Rating::getPartialUpdate($prior, $posterior, 1.0);
        $this->assertEqualsWithDelta($posterior->getMean(), $full->getMean(), 0.000001);
        $this->assertEqualsWithDelta($posterior->getStandardDeviation(), $full->getStandardDeviation(), 0.000001);

        $half = Rating::getPartialUpdate($prior, $posterior, 0.5);
        $this->assertGreaterThan($prior->getMean(), $half->getMean());
        $this->assertLessThan($posterior->getMean(), $half->getMean());
        $this->assertLessThan($prior->getStandardDeviation(), $half->getStandardDeviation());
        $this->assertGreaterThan($posterior->getStandardDeviation(), $half->getStandardDeviation());
    }

    public function test_to_string(): void
    {
        $this->assertSame('mean=25.0000, standardDeviation=8.3333', (string) new Rating(25.0, 25.0 / 3.0));
    }
}
