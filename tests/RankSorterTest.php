<?php

declare(strict_types=1);

namespace Laragod\Skills\Tests;

use InvalidArgumentException;
use Laragod\Skills\RankSorter;

final class RankSorterTest extends TestCase
{
    public function test_sort(): void
    {
        $team1 = ['a' => 1, 'b' => 2];
        $team2 = ['c' => 3, 'd' => 4];
        $team3 = ['e' => 5, 'f' => 6];

        $teams = [$team1, $team2, $team3];

        $teamRanks = [3, 1, 2];

        $sortedTeams = RankSorter::sort($teams, $teamRanks);

        $this->assertSame([$team2, $team3, $team1], $sortedTeams);

        // The arrays are sorted in place as well
        $this->assertSame([$team2, $team3, $team1], $teams);
        $this->assertSame([1, 2, 3], $teamRanks);
    }

    public function test_sort_is_stable_for_tied_ranks(): void
    {
        $teams = ['first', 'second', 'third', 'fourth'];
        $teamRanks = [2, 1, 2, 1];

        RankSorter::sort($teams, $teamRanks);

        $this->assertSame(['second', 'fourth', 'first', 'third'], $teams);
        $this->assertSame([1, 1, 2, 2], $teamRanks);
    }

    public function test_sort_rejects_mismatched_counts(): void
    {
        $teams = ['a', 'b'];
        $teamRanks = [1];

        $this->expectException(InvalidArgumentException::class);

        RankSorter::sort($teams, $teamRanks);
    }
}
