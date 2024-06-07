<?php

declare(strict_types=1);

namespace Laragod\Skills\Tests;

use Laragod\Skills\RankSorter;

class RankSorterTest extends TestCase
{
    public function testSort()
    {
        $team1 = ['a' => 1, 'b' => 2];
        $team2 = ['c' => 3, 'd' => 4];
        $team3 = ['e' => 5, 'f' => 6];

        $teams = [$team1, $team2, $team3];

        $teamRanks = [3, 1, 2];

        $sortedRanks = RankSorter::sort($teams, $teamRanks);

        $this->assertEquals($team2, $sortedRanks[0]);
        $this->assertEquals($team3, $sortedRanks[1]);
        $this->assertEquals($team1, $sortedRanks[2]);

        // Since we are also using a return
        $this->assertEquals($team2, $teams[0]);
        $this->assertEquals($team3, $teams[1]);
        $this->assertEquals($team1, $teams[2]);

        // Since we're passing a reference, but also get a return
        $this->assertEquals($teams[0], $sortedRanks[0]);
        $this->assertEquals($teams[1], $sortedRanks[1]);
        $this->assertEquals($teams[2], $sortedRanks[2]);
    }
}
