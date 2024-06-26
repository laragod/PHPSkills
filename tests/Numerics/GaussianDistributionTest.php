<?php

declare(strict_types=1);

namespace Laragod\Skills\Tests\Numerics;

use Laragod\Skills\Numerics\BasicMath;
use Laragod\Skills\Numerics\GaussianDistribution;
use Laragod\Skills\Tests\TestCase;

class GaussianDistributionTest extends TestCase
{
    const ERROR_TOLERANCE = 0.000001;

    public function testCumulativeTo(): void
    {
        // Verified with WolframAlpha
        // (e.g. http://www.wolframalpha.com/input/?i=CDF%5BNormalDistribution%5B0%2C1%5D%2C+0.5%5D )
        $this->assertEquals(0.691462, GaussianDistribution::cumulativeTo(0.5), '');
    }

    public function testAt(): void
    {
        // Verified with WolframAlpha
        // (e.g. http://www.wolframalpha.com/input/?i=PDF%5BNormalDistribution%5B0%2C1%5D%2C+0.5%5D )
        $this->assertEquals(0.352065, GaussianDistribution::at(0.5), '');
    }

    public function testMultiplication(): void
    {
        // I verified this against the formula at http://www.tina-vision.net/tina-knoppix/tina-memo/2003-003.pdf
        $standardNormal = new GaussianDistribution(0, 1);
        $shiftedGaussian = new GaussianDistribution(2, 3);
        $product = GaussianDistribution::multiply($standardNormal, $shiftedGaussian);

        $this->assertEquals(0.2, $product->getMean(), '');
        $this->assertEquals(3.0 / sqrt(10), $product->getStandardDeviation(), '');

        $m4s5 = new GaussianDistribution(4, 5);
        $m6s7 = new GaussianDistribution(6, 7);

        $product2 = GaussianDistribution::multiply($m4s5, $m6s7);

        $expectedMean = (4 * BasicMath::square(7) + 6 * BasicMath::square(5)) / (BasicMath::square(5) + BasicMath::square(7));
        $this->assertEquals($expectedMean, $product2->getMean(), '');

        $expectedSigma = sqrt(((BasicMath::square(5) * BasicMath::square(7)) / (BasicMath::square(5) + BasicMath::square(7))));
        $this->assertEquals($expectedSigma, $product2->getStandardDeviation(), '');
    }

    public function testDivision(): void
    {
        // Since the multiplication was worked out by hand, we use the same numbers but work backwards
        $product = new GaussianDistribution(0.2, 3.0 / sqrt(10));
        $standardNormal = new GaussianDistribution(0, 1);

        $productDividedByStandardNormal = GaussianDistribution::divide($product, $standardNormal);
        $this->assertEquals(2.0, $productDividedByStandardNormal->getMean(), '');
        $this->assertEquals(3.0, $productDividedByStandardNormal->getStandardDeviation(), '');

        $product2 = new GaussianDistribution((4 * BasicMath::square(7) + 6 * BasicMath::square(5)) / (BasicMath::square(5) + BasicMath::square(7)), sqrt(((BasicMath::square(5) * BasicMath::square(7)) / (BasicMath::square(5) + BasicMath::square(7)))));
        $m4s5 = new GaussianDistribution(4, 5);
        $product2DividedByM4S5 = GaussianDistribution::divide($product2, $m4s5);
        $this->assertEquals(6.0, $product2DividedByM4S5->getMean(), '');
        $this->assertEquals(7.0, $product2DividedByM4S5->getStandardDeviation(), '');
    }

    public function testLogProductNormalization(): void
    {
        // Verified with Ralf Herbrich's F# implementation
        $standardNormal = new GaussianDistribution(0, 1);
        $lpn = GaussianDistribution::logProductNormalization($standardNormal, $standardNormal);
        $this->assertEquals(-1.2655121234846454, $lpn, '');

        $m1s2 = new GaussianDistribution(1, 2);
        $m3s4 = new GaussianDistribution(3, 4);
        $lpn2 = GaussianDistribution::logProductNormalization($m1s2, $m3s4);
        $this->assertEquals(-2.5168046699816684, $lpn2, '');
    }

    public function testLogRatioNormalization(): void
    {
        // Verified with Ralf Herbrich's F# implementation
        $m1s2 = new GaussianDistribution(1, 2);
        $m3s4 = new GaussianDistribution(3, 4);
        $lrn = GaussianDistribution::logRatioNormalization($m1s2, $m3s4);
        $this->assertEquals(2.6157405972171204, $lrn, '');
    }

    public function testAbsoluteDifference(): void
    {
        // Verified with Ralf Herbrich's F# implementation
        $standardNormal = new GaussianDistribution(0, 1);
        $absDiff = GaussianDistribution::absoluteDifference($standardNormal, $standardNormal);
        $this->assertEquals(0.0, $absDiff, '');

        $m1s2 = new GaussianDistribution(1, 2);
        $m3s4 = new GaussianDistribution(3, 4);
        $absDiff2 = GaussianDistribution::absoluteDifference($m1s2, $m3s4);
        $this->assertEquals(0.4330127018922193, $absDiff2, '');
    }
}
