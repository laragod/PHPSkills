<?php

declare(strict_types=1);

namespace Laragod\Skills;

use Exception;

/**
 * Base class for all skill calculator implementations.
 */
abstract class SkillCalculator
{
    private $_supportedOptions;

    private \Laragod\Skills\PlayersRange $_playersPerTeamAllowed;

    private \Laragod\Skills\TeamsRange $_totalTeamsAllowed;

    protected function __construct($supportedOptions, TeamsRange $totalTeamsAllowed, PlayersRange $playerPerTeamAllowed)
    {
        $this->_supportedOptions = $supportedOptions;
        $this->_totalTeamsAllowed = $totalTeamsAllowed;
        $this->_playersPerTeamAllowed = $playerPerTeamAllowed;
    }

    /**
     * Calculates new ratings based on the prior ratings and team ranks.
     *
     * @param  GameInfo  $gameInfo  Parameters for the game.
     * @param  array  $teamsOfPlayerToRatings  A mapping of team players and their ratings.
     * @param  array  $teamRanks  The ranks of the teams where 1 is first place. For a tie, repeat the number (e.g. 1, 2, 2).
     * @return RatingContainer All the players and their new ratings.
     */
    abstract public function calculateNewRatings(GameInfo $gameInfo,
        array $teamsOfPlayerToRatings,
        array $teamRanks): RatingContainer;

    /**
     * Calculates the match quality as the likelihood of all teams drawing.
     *
     * @param  GameInfo  $gameInfo  Parameters for the game.
     * @param  array  $teamsOfPlayerToRatings  A mapping of team players and their ratings.
     * @return float The quality of the match between the teams as a percentage (0% = bad, 100% = well matched).
     */
    abstract public function calculateMatchQuality(GameInfo $gameInfo, array $teamsOfPlayerToRatings): float;

    public function isSupported($option): bool
    {
        return ($this->_supportedOptions & $option) == $option;
    }

    protected function validateTeamCountAndPlayersCountPerTeam(array $teamsOfPlayerToRatings) : void
    {
        $this->validateTeamCountAndPlayersCountPerTeamWithRanges($teamsOfPlayerToRatings, $this->_totalTeamsAllowed, $this->_playersPerTeamAllowed);
    }

    private function validateTeamCountAndPlayersCountPerTeamWithRanges(array $teams,
        TeamsRange $totalTeams,
        PlayersRange $playersPerTeam): void
    {
        $countOfTeams = 0;

        foreach ($teams as $currentTeam) {
            if (! $playersPerTeam->isInRange(count($currentTeam))) {
                throw new Exception('Player count is not in range');
            }
            $countOfTeams++;
        }

        if (! $totalTeams->isInRange($countOfTeams)) {
            throw new Exception('Team range is not in range');
        }
    }
}
