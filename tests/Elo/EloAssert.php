<?php

declare(strict_types=1);

namespace Laragod\Skills\Tests\Elo;

use Laragod\Skills\Elo\EloRating;
use Laragod\Skills\Elo\TwoPlayerEloCalculator;
use Laragod\Skills\GameInfo;
use Laragod\Skills\PairwiseComparison;
use Laragod\Skills\Player;
use Laragod\Skills\Team;
use Laragod\Skills\Tests\TestCase;

final class EloAssert
{
    public const ERROR_TOLERANCE = 0.1;

    public static function assertChessRating(
        TestCase $testClass,
        TwoPlayerEloCalculator $twoPlayerEloCalculator,
        float $player1BeforeRating,
        float $player2BeforeRating,
        PairwiseComparison $player1Result,
        float $player1AfterRating,
        float $player2AfterRating,
    ): void {
        $player1 = new Player('Player1');
        $player2 = new Player('Player2');

        $teams = [
            new Team($player1, new EloRating($player1BeforeRating)),
            new Team($player2, new EloRating($player2BeforeRating)),
        ];

        $chessGameInfo = new GameInfo(1200, 0, 200);

        $result = $twoPlayerEloCalculator->calculateNewRatings($chessGameInfo, $teams, $player1Result->toRanks());

        $testClass->assertEqualsWithDelta($player1AfterRating, $result->getRating($player1)->getMean(), self::ERROR_TOLERANCE);
        $testClass->assertEqualsWithDelta($player2AfterRating, $result->getRating($player2)->getMean(), self::ERROR_TOLERANCE);
    }
}
