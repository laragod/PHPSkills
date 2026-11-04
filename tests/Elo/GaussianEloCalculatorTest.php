<?php

declare(strict_types=1);

namespace Laragod\Skills\Tests\Elo;

use Laragod\Skills\Elo\EloRating;
use Laragod\Skills\Elo\GaussianEloCalculator;
use Laragod\Skills\GameInfo;
use Laragod\Skills\Player;
use Laragod\Skills\Team;
use Laragod\Skills\Tests\TestCase;

final class GaussianEloCalculatorTest extends TestCase
{
    public function test_equal_players_split_the_k_factor_on_a_win(): void
    {
        $calculator = new GaussianEloCalculator;
        $gameInfo = new GameInfo;

        $winner = new Player('winner');
        $loser = new Player('loser');

        $newRatings = $calculator->calculateNewRatings($gameInfo, [
            new Team($winner, new EloRating(1200)),
            new Team($loser, new EloRating(1200)),
        ], [1, 2]);

        // Expected win probability is 0.5, so the change is K * 0.5 = 12
        $this->assertEqualsWithDelta(1212.0, $newRatings->getRating($winner)->getMean(), 0.0001);
        $this->assertEqualsWithDelta(1188.0, $newRatings->getRating($loser)->getMean(), 0.0001);
    }

    public function test_a_draw_between_equal_players_changes_nothing(): void
    {
        $calculator = new GaussianEloCalculator;
        $gameInfo = new GameInfo;

        $player1 = new Player(1);
        $player2 = new Player(2);

        $newRatings = $calculator->calculateNewRatings($gameInfo, [
            new Team($player1, new EloRating(1500)),
            new Team($player2, new EloRating(1500)),
        ], [1, 1]);

        $this->assertEqualsWithDelta(1500.0, $newRatings->getRating($player1)->getMean(), 0.0001);
        $this->assertEqualsWithDelta(1500.0, $newRatings->getRating($player2)->getMean(), 0.0001);
    }

    public function test_win_probability_is_symmetric(): void
    {
        $calculator = new GaussianEloCalculator;
        $gameInfo = new GameInfo;

        $strongWins = $calculator->getPlayerWinProbability($gameInfo, 30.0, 20.0);
        $weakWins = $calculator->getPlayerWinProbability($gameInfo, 20.0, 30.0);

        $this->assertGreaterThan(0.5, $strongWins);
        $this->assertEqualsWithDelta(1.0, $strongWins + $weakWins, 0.000001);
    }
}
