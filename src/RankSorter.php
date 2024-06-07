<?php

declare(strict_types=1);

namespace Laragod\Skills;

/**
 * Helper class to sort ranks in non-decreasing order.
 */
class RankSorter
{
    /**
     * Performs an in-place sort of the items in according to the ranks in non-decreasing order.
     *
     * @param  array  $teams  The items to sort according to the order specified by ranks.
     * @param  array  $teamRanks  The ranks for each item where 1 is first place.
     */
    public static function sort(array &$teams, array &$teamRanks): array
    {
        array_multisort($teamRanks, $teams);

        return $teams;
    }
}
