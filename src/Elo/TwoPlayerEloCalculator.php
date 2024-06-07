<?php

declare(strict_types=1);

namespace Laragod\Skills\Elo;

use Exception;
use Laragod\Skills\GameInfo;
use Laragod\Skills\PairwiseComparison;
use Laragod\Skills\PlayersRange;
use Laragod\Skills\RankSorter;
use Laragod\Skills\RatingContainer;
use Laragod\Skills\SkillCalculator;
use Laragod\Skills\SkillCalculatorSupportedOptions;
use Laragod\Skills\TeamsRange;

abstract class TwoPlayerEloCalculator extends SkillCalculator
{
    protected $_kFactor;

    protected function __construct(KFactor $kFactor)
    {
        parent::__construct(SkillCalculatorSupportedOptions::NONE, TeamsRange::exactly(2), PlayersRange::exactly(1));
        $this->_kFactor = $kFactor;
    }

    public function calculateNewRatings(GameInfo $gameInfo, array $teamsOfPlayerToRatings, array $teamRanks): RatingContainer
    {
        $this->validateTeamCountAndPlayersCountPerTeam($teamsOfPlayerToRatings);
        RankSorter::sort($teamsOfPlayerToRatings, $teamRanks);

        $result = new RatingContainer();
        $isDraw = ($teamRanks[0] === $teamRanks[1]);

        $team1 = $teamsOfPlayerToRatings[0];
        $team2 = $teamsOfPlayerToRatings[1];

        $team1Keys = array_keys($team1);
        $team2Keys = array_keys($team2);

        $player1Key = $team1Keys[0];
        $player2Key = $team2Keys[0];

        $player1 = $team1[$player1Key];
        $player2 = $team2[$player2Key];

        $player1Rating = $player1->getMean();
        $player2Rating = $player2->getMean();

        $newPlayer1Rating = $this->calculateNewRating($gameInfo, $player1Rating, $player2Rating, $isDraw ? PairwiseComparison::DRAW : PairwiseComparison::WIN);
        $newPlayer2Rating = $this->calculateNewRating($gameInfo, $player2Rating, $player1Rating, $isDraw ? PairwiseComparison::DRAW : PairwiseComparison::LOSE);

        $result->setRating($player1, $newPlayer1Rating);
        $result->setRating($player2, $newPlayer2Rating);

        return $result;
    }

    protected function calculateNewRating($gameInfo, $selfRating, $opponentRating, $selfToOpponentComparison): EloRating
    {
        $expectedProbability = $this->getPlayerWinProbability($gameInfo, $selfRating, $opponentRating);
        $actualProbability = $this->getScoreFromComparison($selfToOpponentComparison);
        $k = $this->_kFactor->getValueForRating($selfRating);
        $ratingChange = $k * ($actualProbability - $expectedProbability);
        $newRating = $selfRating + $ratingChange;

        return new EloRating($newRating);
    }

    private static function getScoreFromComparison($comparison)
    {
        switch ($comparison) {
            case PairwiseComparison::WIN:
                return 1;
            case PairwiseComparison::DRAW:
                return 0.5;
            case PairwiseComparison::LOSE:
                return 0;
            default:
                throw new Exception('Unexpected comparison');
        }
    }

    abstract public function getPlayerWinProbability(GameInfo $gameInfo, $playerRating, $opponentRating);

    public function calculateMatchQuality(GameInfo $gameInfo, array $teamsOfPlayerToRatings): float
    {
        $this->validateTeamCountAndPlayersCountPerTeam($teamsOfPlayerToRatings);
        $team1 = $teamsOfPlayerToRatings[0];
        $team2 = $teamsOfPlayerToRatings[1];

        $player1 = $team1[0];
        $player2 = $team2[0];

        $player1Rating = $player1->getMean();
        $player2Rating = $player2->getMean();

        $ratingDifference = $player1Rating - $player2Rating;

        // The TrueSkill paper mentions that they used s1 - s2 (rating difference) to
        // determine match quality. I convert that to a percentage as a delta from 50%
        // using the cumulative density function of the specific curve being used
        $deltaFrom50Percent = abs($this->getPlayerWinProbability($gameInfo, $player1Rating, $player2Rating) - 0.5);

        return (0.5 - $deltaFrom50Percent) / 0.5;
    }
}
