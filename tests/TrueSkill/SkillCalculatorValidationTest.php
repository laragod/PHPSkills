<?php

declare(strict_types=1);

namespace Laragod\Skills\Tests\TrueSkill;

use InvalidArgumentException;
use Laragod\Skills\GameInfo;
use Laragod\Skills\Player;
use Laragod\Skills\SkillCalculator;
use Laragod\Skills\SkillCalculatorSupportedOptions;
use Laragod\Skills\Team;
use Laragod\Skills\Tests\TestCase;
use Laragod\Skills\TrueSkill\FactorGraphTrueSkillCalculator;
use Laragod\Skills\TrueSkill\TwoPlayerTrueSkillCalculator;
use Laragod\Skills\TrueSkill\TwoTeamTrueSkillCalculator;
use PHPUnit\Framework\Attributes\DataProvider;

final class SkillCalculatorValidationTest extends TestCase
{
    /**
     * @return iterable<string, array{SkillCalculator}>
     */
    public static function calculators(): iterable
    {
        yield 'two player' => [new TwoPlayerTrueSkillCalculator];
        yield 'two team' => [new TwoTeamTrueSkillCalculator];
        yield 'factor graph' => [new FactorGraphTrueSkillCalculator];
    }

    #[DataProvider('calculators')]
    public function test_a_single_team_is_rejected(SkillCalculator $calculator): void
    {
        $gameInfo = new GameInfo;

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculateNewRatings($gameInfo, [new Team(new Player(1), $gameInfo->getDefaultRating())], [1]);
    }

    #[DataProvider('calculators')]
    public function test_rank_count_must_match_team_count(SkillCalculator $calculator): void
    {
        $gameInfo = new GameInfo;
        $teams = [
            new Team(new Player(1), $gameInfo->getDefaultRating()),
            new Team(new Player(2), $gameInfo->getDefaultRating()),
        ];

        $this->expectException(InvalidArgumentException::class);

        $calculator->calculateNewRatings($gameInfo, $teams, [1]);
    }

    public function test_two_player_calculator_rejects_teams_with_more_than_one_player(): void
    {
        $gameInfo = new GameInfo;
        $teams = [
            (new Team)
                ->addPlayer(new Player(1), $gameInfo->getDefaultRating())
                ->addPlayer(new Player(2), $gameInfo->getDefaultRating()),
            new Team(new Player(3), $gameInfo->getDefaultRating()),
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('exactly 1 player(s) per team');

        (new TwoPlayerTrueSkillCalculator)->calculateNewRatings($gameInfo, $teams, [1, 2]);
    }

    public function test_two_team_calculator_rejects_three_teams(): void
    {
        $gameInfo = new GameInfo;
        $teams = [
            new Team(new Player(1), $gameInfo->getDefaultRating()),
            new Team(new Player(2), $gameInfo->getDefaultRating()),
            new Team(new Player(3), $gameInfo->getDefaultRating()),
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('exactly 2 teams');

        (new TwoTeamTrueSkillCalculator)->calculateNewRatings($gameInfo, $teams, [1, 2, 3]);
    }

    public function test_supported_options(): void
    {
        $factorGraph = new FactorGraphTrueSkillCalculator;
        $twoPlayer = new TwoPlayerTrueSkillCalculator;

        $this->assertTrue($factorGraph->isSupported(SkillCalculatorSupportedOptions::PARTIAL_PLAY));
        $this->assertTrue($factorGraph->isSupported(SkillCalculatorSupportedOptions::PARTIAL_UPDATE));
        $this->assertFalse($twoPlayer->isSupported(SkillCalculatorSupportedOptions::PARTIAL_PLAY));
        $this->assertTrue($twoPlayer->isSupported(SkillCalculatorSupportedOptions::NONE));
    }

    public function test_teams_may_be_given_in_any_order(): void
    {
        $gameInfo = new GameInfo;
        $calculator = new FactorGraphTrueSkillCalculator;

        $winner = new Player('winner');
        $loser = new Player('loser');

        // The loser's team is listed first, but the ranks say it came second
        $newRatings = $calculator->calculateNewRatings($gameInfo, [
            new Team($loser, $gameInfo->getDefaultRating()),
            new Team($winner, $gameInfo->getDefaultRating()),
        ], [2, 1]);

        $this->assertEqualsWithDelta(29.396, $newRatings->getRating($winner)->getMean(), 0.01);
        $this->assertEqualsWithDelta(20.604, $newRatings->getRating($loser)->getMean(), 0.01);
    }
}
