<?php

declare(strict_types=1);

namespace Laragod\Skills;

use InvalidArgumentException;

/**
 * Base class for all skill calculator implementations.
 */
abstract class SkillCalculator
{
    /**
     * @param  int  $supportedOptions  A bit mask of SkillCalculatorSupportedOptions constants.
     */
    protected function __construct(
        private readonly int $supportedOptions,
        private readonly TeamsRange $totalTeamsAllowed,
        private readonly PlayersRange $playersPerTeamAllowed,
    ) {}

    /**
     * Calculates new ratings based on the prior ratings and team ranks.
     *
     * @param  GameInfo  $gameInfo  Parameters for the game.
     * @param  list<Team>  $teams  The teams that played, each mapping its players to their current ratings.
     * @param  list<int>  $teamRanks  The rank of each team where 1 is first place. For a tie, repeat the number (e.g. 1, 2, 2).
     * @return RatingContainer All the players and their new ratings.
     */
    abstract public function calculateNewRatings(GameInfo $gameInfo, array $teams, array $teamRanks): RatingContainer;

    /**
     * Calculates the match quality as the likelihood of all teams drawing.
     *
     * @param  GameInfo  $gameInfo  Parameters for the game.
     * @param  list<Team>  $teams  The teams that would play, each mapping its players to their current ratings.
     * @return float The quality of the match between the teams from 0.0 (bad) to 1.0 (well matched).
     */
    abstract public function calculateMatchQuality(GameInfo $gameInfo, array $teams): float;

    /**
     * @param  int  $option  One of the SkillCalculatorSupportedOptions constants.
     */
    public function isSupported(int $option): bool
    {
        return ($this->supportedOptions & $option) === $option;
    }

    /**
     * @param  list<Team>  $teams
     */
    protected function validateTeamCountAndPlayersCountPerTeam(array $teams): void
    {
        foreach ($teams as $index => $team) {
            if (! $this->playersPerTeamAllowed->isInRange($team->count())) {
                throw new InvalidArgumentException(sprintf(
                    'Team %d has %d player(s); this calculator supports %s player(s) per team',
                    $index,
                    $team->count(),
                    $this->playersPerTeamAllowed,
                ));
            }
        }

        if (! $this->totalTeamsAllowed->isInRange(count($teams))) {
            throw new InvalidArgumentException(sprintf(
                '%d team(s) given; this calculator supports %s teams',
                count($teams),
                $this->totalTeamsAllowed,
            ));
        }
    }

    /**
     * @param  list<Team>  $teams
     * @param  list<int>  $teamRanks
     */
    protected function validateTeamRanks(array $teams, array $teamRanks): void
    {
        if (count($teams) !== count($teamRanks)) {
            throw new InvalidArgumentException(sprintf('Expected one rank per team, got %d teams and %d ranks', count($teams), count($teamRanks)));
        }
    }
}
