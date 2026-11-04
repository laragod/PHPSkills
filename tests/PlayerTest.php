<?php

declare(strict_types=1);

namespace Laragod\Skills\Tests;

use InvalidArgumentException;
use Laragod\Skills\PartialPlay;
use Laragod\Skills\Player;
use stdClass;

final class PlayerTest extends TestCase
{
    public function test_defaults_to_full_play_and_full_updates(): void
    {
        $player = new Player('alice');

        $this->assertSame('alice', $player->getId());
        $this->assertSame(1.0, $player->getPartialPlayPercentage());
        $this->assertSame(1.0, $player->getPartialUpdatePercentage());
        $this->assertSame('alice', (string) $player);
    }

    public function test_accepts_integer_ids_and_partial_percentages(): void
    {
        $player = new Player(42, 0.25, 0.5);

        $this->assertSame(42, $player->getId());
        $this->assertSame('42', (string) $player);
        $this->assertSame(0.25, $player->getPartialPlayPercentage());
        $this->assertSame(0.5, $player->getPartialUpdatePercentage());
    }

    public function test_partial_play_percentage_must_be_between_zero_and_one(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('partialPlayPercentage');

        new Player(1, 1.5);
    }

    public function test_partial_update_percentage_must_be_between_zero_and_one(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('partialUpdatePercentage');

        new Player(1, 1.0, -0.1);
    }

    public function test_partial_play_helper_never_returns_exactly_zero(): void
    {
        $this->assertSame(1.0, PartialPlay::getPartialPlayPercentage(new Player(1)));
        $this->assertSame(0.5, PartialPlay::getPartialPlayPercentage(new Player(1, 0.5)));
        $this->assertGreaterThan(0.0, PartialPlay::getPartialPlayPercentage(new Player(1, 0.0)));
        $this->assertSame(1.0, PartialPlay::getPartialPlayPercentage(new stdClass));
    }
}
