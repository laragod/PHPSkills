<?php

declare(strict_types=1);

namespace Laragod\Skills\Tests\Elo;

use Laragod\Skills\Elo\EloRating;
use Laragod\Skills\Elo\FideEloCalculator;
use Laragod\Skills\Elo\ProvisionalFideKFactor;
use Laragod\Skills\GameInfo;
use Laragod\Skills\PairwiseComparison;
use Laragod\Skills\Player;
use Laragod\Skills\Team;
use Laragod\Skills\Tests\TestCase;

final class FideEloCalculatorTest extends TestCase
{
    public function test_fide_provisional_elo_calculator(): void
    {
        // verified against http://ratings.fide.com/calculator_rtd.phtml
        $calc = new FideEloCalculator(new ProvisionalFideKFactor);

        EloAssert::assertChessRating($this, $calc, 1200, 1500, PairwiseComparison::WIN, 1221.25, 1478.75);
        EloAssert::assertChessRating($this, $calc, 1200, 1500, PairwiseComparison::DRAW, 1208.75, 1491.25);
        EloAssert::assertChessRating($this, $calc, 1200, 1500, PairwiseComparison::LOSE, 1196.25, 1503.75);
    }

    public function test_fide_non_provisional_elo_calculator(): void
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

    public function test_provisional_factory_uses_provisional_k_factor(): void
    {
        $provisional = FideEloCalculator::createWithProvisionalKFactor();
        $established = FideEloCalculator::createWithDefaultKFactor();

        EloAssert::assertChessRating($this, $provisional, 1200, 1500, PairwiseComparison::WIN, 1221.25, 1478.75);
        EloAssert::assertChessRating($this, $established, 1200, 1500, PairwiseComparison::WIN, 1212.75, 1487.25);
    }

    public function test_match_quality_is_perfect_for_equal_ratings_and_drops_with_distance(): void
    {
        $calc = FideEloCalculator::createWithDefaultKFactor();
        $gameInfo = new GameInfo(1200, 0, 200);

        $equal = $calc->calculateMatchQuality($gameInfo, [
            new Team(new Player(1), $gameInfo->getDefaultRating()),
            new Team(new Player(2), $gameInfo->getDefaultRating()),
        ]);

        $this->assertEqualsWithDelta(1.0, $equal, 0.0001);

        $lopsided = $calc->calculateMatchQuality($gameInfo, [
            new Team(new Player(1), new EloRating(2400)),
            new Team(new Player(2), new EloRating(1200)),
        ]);

        $this->assertLessThan(0.01, $lopsided);
    }
}
