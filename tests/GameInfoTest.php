<?php

declare(strict_types=1);

namespace Laragod\Skills\Tests;

use InvalidArgumentException;
use Laragod\Skills\GameInfo;

final class GameInfoTest extends TestCase
{
    public function test_defaults_follow_the_true_skill_paper(): void
    {
        $gameInfo = new GameInfo;

        $this->assertSame(25.0, $gameInfo->getInitialMean());
        $this->assertEqualsWithDelta(25.0 / 3.0, $gameInfo->getInitialStandardDeviation(), 0.000001);
        $this->assertEqualsWithDelta(25.0 / 6.0, $gameInfo->getBeta(), 0.000001);
        $this->assertEqualsWithDelta(25.0 / 300.0, $gameInfo->getDynamicsFactor(), 0.000001);
        $this->assertSame(0.10, $gameInfo->getDrawProbability());

        $defaultRating = $gameInfo->getDefaultRating();
        $this->assertSame(25.0, $defaultRating->getMean());
        $this->assertEqualsWithDelta(25.0 / 3.0, $defaultRating->getStandardDeviation(), 0.000001);
    }

    public function test_custom_values_are_kept(): void
    {
        $gameInfo = new GameInfo(1500, 500, 250, 5, 0.25);

        $this->assertSame(1500.0, $gameInfo->getInitialMean());
        $this->assertSame(500.0, $gameInfo->getInitialStandardDeviation());
        $this->assertSame(250.0, $gameInfo->getBeta());
        $this->assertSame(5.0, $gameInfo->getDynamicsFactor());
        $this->assertSame(0.25, $gameInfo->getDrawProbability());
    }

    public function test_draw_probability_must_be_a_probability(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new GameInfo(drawProbability: 1.5);
    }
}
