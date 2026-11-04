<?php

declare(strict_types=1);

namespace Laragod\Skills\Tests\Numerics;

use Laragod\Skills\Numerics\BasicMath;
use Laragod\Skills\Tests\TestCase;

final class BasicMathTest extends TestCase
{
    public function test_square(): void
    {
        $this->assertSame(1.0, BasicMath::square(1));
        $this->assertEqualsWithDelta(1.44, BasicMath::square(1.2), 0.0000001);
        $this->assertSame(4.0, BasicMath::square(2));
        $this->assertSame(6.25, BasicMath::square(-2.5));
    }

    public function test_sum_applies_the_callback_to_every_item(): void
    {
        $this->assertSame(0.0, BasicMath::sum([], static fn (float $x): float => $x));
        $this->assertSame(14.0, BasicMath::sum([1.0, 2.0, 3.0], static fn (float $x): float => $x * $x));
    }
}
