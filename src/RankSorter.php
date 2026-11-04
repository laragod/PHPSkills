<?php

declare(strict_types=1);

namespace Laragod\Skills;

use InvalidArgumentException;

/**
 * Sorts teams by their ranks in non-decreasing order.
 */
final class RankSorter
{
    /**
     * Performs an in-place, stable sort of the teams according to their ranks (1 is first place).
     *
     * Both arrays are re-indexed so that $teams[$i] has rank $teamRanks[$i] afterwards.
     *
     * @template T
     *
     * @param  list<T>  $teams  The items to sort.
     * @param  list<int>  $teamRanks  The rank of each item, where 1 is first place.
     * @return list<T> The sorted items (the same array that was passed by reference).
     */
    public static function sort(array &$teams, array &$teamRanks): array
    {
        if (count($teams) !== count($teamRanks)) {
            throw new InvalidArgumentException(sprintf('Expected one rank per team, got %d teams and %d ranks', count($teams), count($teamRanks)));
        }

        $indices = array_keys($teamRanks);
        usort($indices, static fn (int $left, int $right): int => $teamRanks[$left] <=> $teamRanks[$right]);

        $sortedTeams = [];
        $sortedRanks = [];

        foreach ($indices as $index) {
            $sortedTeams[] = $teams[$index];
            $sortedRanks[] = $teamRanks[$index];
        }

        $teams = $sortedTeams;
        $teamRanks = $sortedRanks;

        return $teams;
    }
}
