<?php

declare(strict_types=1);

namespace Laragod\Skills\Tests\Elo;

use Laragod\Skills\Elo\FideEloCalculator;
use Laragod\Skills\Elo\ProvisionalFideKFactor;
use Laragod\Skills\PairwiseComparison;
use Laragod\Skills\Tests\TestCase;

class FideEloCalculatorTest extends TestCase
{
    public function testFideProvisionalEloCalculator()
    {
        // verified against http://ratings.fide.com/calculator_rtd.phtml
        $calc = new FideEloCalculator(new ProvisionalFideKFactor());

        EloAssert::assertChessRating($this, $calc, 1200, 1500, PairwiseComparison::WIN, 1221.25, 1478.75);
        EloAssert::assertChessRating($this, $calc, 1200, 1500, PairwiseComparison::DRAW, 1208.75, 1491.25);
        EloAssert::assertChessRating($this, $calc, 1200, 1500, PairwiseComparison::LOSE, 1196.25, 1503.75);
    }

    public function testFideNonProvisionalEloCalculator()
    {
        // verified against http://ratings.fide.com/calculator_rtd.phtml
        $calc = FideEloCalculator::createWithDefaultKFactor();

        EloAssert::assertChessRating($this, $calc, 1200, 1200, PairwiseComparison::WIN, 1207.5, 1192.5);
        EloAssert::assertChessRating($this, $calc, 1200, 1200, PairwiseComparison::DRAW, 1200, 1200);
        EloAssert::assertChessRating($this, $calc, 1200, 1200, PairwiseComparison::LOSE, 1192.5, 1207.5);

        EloAssert::assertChessRating($this, $calc, 2600, 2500, PairwiseComparison::WIN, 2603.6, 2496.4);
        EloAssert::assertChessRating($this, $calc, 2600, 2500, PairwiseComparison::DRAW, 2598.6, 2501.4);
        EloAssert::assertChessRating($this, $calc, 2600, 2500, PairwiseComparison::LOSE, 2593.6, 2506.4);
    }
}
