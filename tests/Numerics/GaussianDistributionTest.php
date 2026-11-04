<?php

declare(strict_types=1);

namespace Laragod\Skills\Tests\Numerics;

use Laragod\Skills\Numerics\BasicMath;
use Laragod\Skills\Numerics\GaussianDistribution;
use Laragod\Skills\Tests\TestCase;

final class GaussianDistributionTest extends TestCase
{
    private const ERROR_TOLERANCE = 0.000001;

    public function test_cumulative_to(): void
    {
        // Verified with WolframAlpha
        // (e.g. http://www.wolframalpha.com/input/?i=CDF%5BNormalDistribution%5B0%2C1%5D%2C+0.5%5D )
        $this->assertEqualsWithDelta(0.691462, GaussianDistribution::cumulativeTo(0.5), self::ERROR_TOLERANCE);
    }

    public function test_inverse_cumulative_to_inverts_cumulative_to(): void
    {
        $this->assertEqualsWithDelta(0.5, GaussianDistribution::inverseCumulativeTo(0.691462), 0.00001);
        $this->assertEqualsWithDelta(0.0, GaussianDistribution::inverseCumulativeTo(0.5), self::ERROR_TOLERANCE);
    }

    public function test_at(): void
    {
        // Verified with WolframAlpha
        // (e.g. http://www.wolframalpha.com/input/?i=PDF%5BNormalDistribution%5B0%2C1%5D%2C+0.5%5D )
        $this->assertEqualsWithDelta(0.352065, GaussianDistribution::at(0.5), self::ERROR_TOLERANCE);
    }

    public function test_multiplication(): void
    {
        // I verified this against the formula at http://www.tina-vision.net/tina-knoppix/tina-memo/2003-003.pdf
        $standardNormal = new GaussianDistribution(0, 1);
        $shiftedGaussian = new GaussianDistribution(2, 3);
        $product = GaussianDistribution::multiply($standardNormal, $shiftedGaussian);

        $this->assertEqualsWithDelta(0.2, $product->getMean(), self::ERROR_TOLERANCE);
        $this->assertEqualsWithDelta(3.0 / sqrt(10), $product->getStandardDeviation(), self::ERROR_TOLERANCE);

        $m4s5 = new GaussianDistribution(4, 5);
        $m6s7 = new GaussianDistribution(6, 7);

        $product2 = GaussianDistribution::multiply($m4s5, $m6s7);

        $expectedMean = (4 * BasicMath::square(7) + 6 * BasicMath::square(5)) / (BasicMath::square(5) + BasicMath::square(7));
        $this->assertEqualsWithDelta($expectedMean, $product2->getMean(), self::ERROR_TOLERANCE);

        $expectedSigma = sqrt(((BasicMath::square(5) * BasicMath::square(7)) / (BasicMath::square(5) + BasicMath::square(7))));
        $this->assertEqualsWithDelta($expectedSigma, $product2->getStandardDeviation(), self::ERROR_TOLERANCE);
    }

    public function test_division(): void
    {
        // Since the multiplication was worked out by hand, we use the same numbers but work backwards
        $product = new GaussianDistribution(0.2, 3.0 / sqrt(10));
        $standardNormal = new GaussianDistribution(0, 1);

        $productDividedByStandardNormal = GaussianDistribution::divide($product, $standardNormal);
        $this->assertEqualsWithDelta(2.0, $productDividedByStandardNormal->getMean(), self::ERROR_TOLERANCE);
        $this->assertEqualsWithDelta(3.0, $productDividedByStandardNormal->getStandardDeviation(), self::ERROR_TOLERANCE);

        $product2 = new GaussianDistribution((4 * BasicMath::square(7) + 6 * BasicMath::square(5)) / (BasicMath::square(5) + BasicMath::square(7)), sqrt(((BasicMath::square(5) * BasicMath::square(7)) / (BasicMath::square(5) + BasicMath::square(7)))));
        $m4s5 = new GaussianDistribution(4, 5);
        $product2DividedByM4S5 = GaussianDistribution::divide($product2, $m4s5);
        $this->assertEqualsWithDelta(6.0, $product2DividedByM4S5->getMean(), self::ERROR_TOLERANCE);
        $this->assertEqualsWithDelta(7.0, $product2DividedByM4S5->getStandardDeviation(), self::ERROR_TOLERANCE);
    }

    public function test_log_product_normalization(): void
    {
        // Verified with Ralf Herbrich's F# implementation
        $standardNormal = new GaussianDistribution(0, 1);
        $lpn = GaussianDistribution::logProductNormalization($standardNormal, $standardNormal);
        $this->assertEqualsWithDelta(-1.2655121234846454, $lpn, self::ERROR_TOLERANCE);

        $m1s2 = new GaussianDistribution(1, 2);
        $m3s4 = new GaussianDistribution(3, 4);
        $lpn2 = GaussianDistribution::logProductNormalization($m1s2, $m3s4);
        $this->assertEqualsWithDelta(-2.5168046699816684, $lpn2, self::ERROR_TOLERANCE);
    }

    public function test_log_ratio_normalization(): void
    {
        // Verified with Ralf Herbrich's F# implementation
        $m1s2 = new GaussianDistribution(1, 2);
        $m3s4 = new GaussianDistribution(3, 4);
        $lrn = GaussianDistribution::logRatioNormalization($m1s2, $m3s4);
        $this->assertEqualsWithDelta(2.6157405972171204, $lrn, self::ERROR_TOLERANCE);
    }

    public function test_absolute_difference(): void
    {
        // Verified with Ralf Herbrich's F# implementation
        $standardNormal = new GaussianDistribution(0, 1);
        $absDiff = GaussianDistribution::absoluteDifference($standardNormal, $standardNormal);
        $this->assertEqualsWithDelta(0.0, $absDiff, self::ERROR_TOLERANCE);

        $m1s2 = new GaussianDistribution(1, 2);
        $m3s4 = new GaussianDistribution(3, 4);
        $absDiff2 = GaussianDistribution::absoluteDifference($m1s2, $m3s4);
        $this->assertEqualsWithDelta(0.4330127018922193, $absDiff2, self::ERROR_TOLERANCE);
    }

    public function test_from_precision_mean_round_trips(): void
    {
        $original = new GaussianDistribution(25.0, 25.0 / 3.0);
        $rebuilt = GaussianDistribution::fromPrecisionMean($original->getPrecisionMean(), $original->getPrecision());

        $this->assertEqualsWithDelta(25.0, $rebuilt->getMean(), self::ERROR_TOLERANCE);
        $this->assertEqualsWithDelta(25.0 / 3.0, $rebuilt->getStandardDeviation(), self::ERROR_TOLERANCE);
        $this->assertEqualsWithDelta($original->getVariance(), $rebuilt->getVariance(), self::ERROR_TOLERANCE);
    }

    public function test_zero_precision_is_an_uninformative_prior(): void
    {
        $prior = GaussianDistribution::fromPrecisionMean(0.0, 0.0);

        $this->assertSame(0.0, $prior->getPrecision());
        $this->assertTrue(is_infinite($prior->getVariance()));
        $this->assertTrue(is_nan($prior->getMean()));

        // Multiplying by an uninformative prior leaves a distribution untouched
        $m1s2 = new GaussianDistribution(1, 2);
        $product = GaussianDistribution::multiply($m1s2, $prior);

        $this->assertEqualsWithDelta(1.0, $product->getMean(), self::ERROR_TOLERANCE);
        $this->assertEqualsWithDelta(2.0, $product->getStandardDeviation(), self::ERROR_TOLERANCE);
    }

    public function test_to_string(): void
    {
        $this->assertSame('mean=1.0000 standardDeviation=2.0000', (string) new GaussianDistribution(1, 2));
    }
}
