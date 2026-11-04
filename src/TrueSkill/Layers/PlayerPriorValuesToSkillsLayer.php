<?php

declare(strict_types=1);

namespace Laragod\Skills\TrueSkill\Layers;

use Laragod\Skills\FactorGraphs\KeyedVariable;
use Laragod\Skills\FactorGraphs\ScheduleSequence;
use Laragod\Skills\FactorGraphs\ScheduleStep;
use Laragod\Skills\FactorGraphs\Variable;
use Laragod\Skills\Numerics\BasicMath;
use Laragod\Skills\Numerics\GaussianDistribution;
use Laragod\Skills\Player;
use Laragod\Skills\Rating;
use Laragod\Skills\Team;
use Laragod\Skills\TrueSkill\Factors\GaussianPriorFactor;
use Laragod\Skills\TrueSkill\TrueSkillFactorGraph;

/**
 * Turns every player's prior rating into a skill variable.
 *
 * There is intentionally no posterior schedule since the only purpose here is to start the process.
 *
 * @extends TrueSkillFactorGraphLayer<Variable<GaussianDistribution>, KeyedVariable<Player, GaussianDistribution>>
 */
final class PlayerPriorValuesToSkillsLayer extends TrueSkillFactorGraphLayer
{
    /**
     * @param  list<Team>  $teams
     */
    public function __construct(
        TrueSkillFactorGraph $parentGraph,
        private readonly array $teams,
    ) {
        parent::__construct($parentGraph);
    }

    public function buildLayer(): void
    {
        foreach ($this->teams as $team) {
            $currentTeamSkills = [];

            foreach ($team as $player => $rating) {
                $playerSkill = $this->createSkillOutputVariable($player);
                $this->addLayerFactor($this->createPriorFactor($rating, $playerSkill));
                $currentTeamSkills[] = $playerSkill;
            }

            $this->addOutputVariablesGroup($currentTeamSkills);
        }
    }

    /**
     * @return ScheduleSequence<GaussianDistribution>
     */
    public function createPriorSchedule(): ScheduleSequence
    {
        return $this->scheduleSequence(
            array_map(
                static fn (GaussianPriorFactor $prior): ScheduleStep => new ScheduleStep('Prior to Skill Step', $prior, 0),
                $this->getPriorFactors()
            ),
            'All priors'
        );
    }

    /**
     * @return list<GaussianPriorFactor>
     */
    private function getPriorFactors(): array
    {
        $priors = [];

        foreach ($this->getLocalFactors() as $factor) {
            if ($factor instanceof GaussianPriorFactor) {
                $priors[] = $factor;
            }
        }

        return $priors;
    }

    /**
     * @param  Variable<GaussianDistribution>  $skillsVariable
     */
    private function createPriorFactor(Rating $priorRating, Variable $skillsVariable): GaussianPriorFactor
    {
        return new GaussianPriorFactor(
            $priorRating->getMean(),
            BasicMath::square($priorRating->getStandardDeviation())
                + BasicMath::square($this->getParentFactorGraph()->getGameInfo()->getDynamicsFactor()),
            $skillsVariable
        );
    }

    /**
     * @return KeyedVariable<Player, GaussianDistribution>
     */
    private function createSkillOutputVariable(Player $player): KeyedVariable
    {
        return $this->getParentFactorGraph()->getVariableFactory()->createKeyedVariable($player, sprintf("%s's skill", $player));
    }
}
