<?php

declare(strict_types=1);

namespace Laragod\Skills\Tests;

use Laragod\Skills\PairwiseComparison;

final class PairwiseComparisonTest extends TestCase
{
    public function test_values_double_as_mean_update_multipliers(): void
    {
        $this->assertSame(1, PairwiseComparison::WIN->value);
        $this->assertSame(0, PairwiseComparison::DRAW->value);
        $this->assertSame(-1, PairwiseComparison::LOSE->value);
    }

    public function test_to_ranks(): void
    {
        $this->assertSame([1, 2], PairwiseComparison::WIN->toRanks());
        $this->assertSame([1, 1], PairwiseComparison::DRAW->toRanks());
        $this->assertSame([2, 1], PairwiseComparison::LOSE->toRanks());
        $this->assertSame([2, 1], PairwiseComparison::getRankFromComparison(PairwiseComparison::LOSE));
    }
}
