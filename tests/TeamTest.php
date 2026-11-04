<?php

declare(strict_types=1);

namespace Laragod\Skills\Tests;

use InvalidArgumentException;
use Laragod\Skills\Player;
use Laragod\Skills\Rating;
use Laragod\Skills\Team;
use Laragod\Skills\Teams;
use OutOfBoundsException;

final class TeamTest extends TestCase
{
    public function test_players_keep_their_insertion_order(): void
    {
        $alice = new Player('alice');
        $bob = new Player('bob');
        $aliceRating = new Rating(25, 8);
        $bobRating = new Rating(30, 3);

        $team = (new Team)
            ->addPlayer($alice, $aliceRating)
            ->addPlayer($bob, $bobRating);

        $this->assertCount(2, $team);
        $this->assertSame([$alice, $bob], $team->getAllPlayers());
        $this->assertSame([$aliceRating, $bobRating], $team->getAllRatings());
        $this->assertSame($bobRating, $team->getRating($bob));
        $this->assertTrue($team->hasPlayer($alice));
        $this->assertFalse($team->hasPlayer(new Player('alice')));
    }

    public function test_team_can_be_iterated_as_player_to_rating_pairs(): void
    {
        $alice = new Player('alice');
        $rating = new Rating(25, 8);
        $team = new Team($alice, $rating);

        $pairs = [];

        foreach ($team as $player => $playerRating) {
            $pairs[] = [$player, $playerRating];
        }

        $this->assertSame([[$alice, $rating]], $pairs);
    }

    public function test_setting_a_rating_twice_overwrites_it(): void
    {
        $alice = new Player('alice');
        $team = new Team($alice, new Rating(25, 8));

        $updated = new Rating(27, 7);
        $team->setRating($alice, $updated);

        $this->assertCount(1, $team);
        $this->assertSame($updated, $team->getRating($alice));
    }

    public function test_unknown_player_is_rejected(): void
    {
        $team = new Team(new Player('alice'), new Rating(25, 8));

        $this->expectException(OutOfBoundsException::class);

        $team->getRating(new Player('bob'));
    }

    public function test_player_without_rating_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Team(new Player('alice'));
    }

    public function test_concat_builds_a_list_of_teams(): void
    {
        $team1 = new Team(new Player(1), new Rating(25, 8));
        $team2 = new Team(new Player(2), new Rating(25, 8));

        $this->assertSame([$team1, $team2], Teams::concat($team1, $team2));
        $this->assertSame([], Teams::concat());
    }
}
