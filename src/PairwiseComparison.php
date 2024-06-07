<?php

declare(strict_types=1);

namespace Laragod\Skills;

/**
 * Represents a comparison between two players.
 *
 * @internal The actual values for the enum were chosen so that the also correspond to the multiplier for updates to means.
 */
class PairwiseComparison
{
    const WIN = 1;

    const DRAW = 0;

    const LOSE = -1;

    public static function getRankFromComparison($comparison): array
    {
        switch ($comparison) {
            case PairwiseComparison::WIN:
                return [1, 2];
            case PairwiseComparison::LOSE:
                return [2, 1];
            default:
                return [1, 1];
        }
    }
}
