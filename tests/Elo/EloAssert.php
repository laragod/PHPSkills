<?php

declare(strict_types=1);

namespace Laragod\Skills\Tests\Elo;

use Laragod\Skills\Elo\EloRating;
use Laragod\Skills\Elo\FideEloCalculator;
use Laragod\Skills\GameInfo;
use Laragod\Skills\PairwiseComparison;
use Laragod\Skills\Tests\TestCase;

class EloAssert
{
    const ERROR_TOLERANCE = 0.1;

    public static function assertChessRating(
        TestCase $testClass,
        FideEloCalculator $twoPlayerEloCalculator,
        $player1BeforeRating,
        $player2BeforeRating,
        $player1Result,
        $player1AfterRating,
        $player2AfterRating): void
    {
        $player1 = 'Player1';
        $player2 = 'Player2';

        $teams = [
            [$player1 => new EloRating($player1BeforeRating)],
            [$player2 => new EloRating($player2BeforeRating)],
        ];

        $chessGameInfo = new GameInfo(1200, 0, 200);

        $ranks = PairwiseComparison::getRankFromComparison($player1Result);

        $result = $twoPlayerEloCalculator->calculateNewRatings(
            $chessGameInfo,
            $teams,
            $ranks
        );

        $testClass->assertEquals($player1AfterRating, $result[$player1]->getMean(), '');
        $testClass->assertEquals($player2AfterRating, $result[$player2]->getMean(), '');
    }
}
