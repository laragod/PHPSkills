<?php

declare(strict_types=1);

namespace Laragod\Skills;

/**
 * The outcome of a game between two players (or teams) from the first one's point of view.
 *
 * The backing values are chosen so that they also act as the multiplier for mean updates.
 */
enum PairwiseComparison: int
{
    case WIN = 1;
    case DRAW = 0;
    case LOSE = -1;

    /**
     * The ranks of both sides, where 1 is first place and a draw repeats the number.
     *
     * @return array{int, int}
     */
    public function toRanks(): array
    {
        return match ($this) {
            self::WIN => [1, 2],
            self::LOSE => [2, 1],
            self::DRAW => [1, 1],
        };
    }

    /**
     * @return array{int, int}
     */
    public static function getRankFromComparison(self $comparison): array
    {
        return $comparison->toRanks();
    }
}
