<?php

declare(strict_types=1);

namespace Laragod\Skills\TrueSkill\Layers;

use Laragod\Skills\FactorGraphs\Factor;
use Laragod\Skills\FactorGraphs\KeyedVariable;
use Laragod\Skills\FactorGraphs\ScheduleSequence;
use Laragod\Skills\FactorGraphs\ScheduleStep;
use Laragod\Skills\Numerics\BasicMath;
use Laragod\Skills\Numerics\GaussianDistribution;
use Laragod\Skills\Player;
use Laragod\Skills\TrueSkill\Factors\GaussianLikelihoodFactor;

/**
 * Connects every player's skill to a performance variable, adding the game's beta as uncertainty.
 *
 * @extends TrueSkillFactorGraphLayer<KeyedVariable<Player, GaussianDistribution>, KeyedVariable<Player, GaussianDistribution>>
 */
final class PlayerSkillsToPerformancesLayer extends TrueSkillFactorGraphLayer
{
    public function buildLayer(): void
    {
        foreach ($this->getInputVariablesGroups() as $currentTeam) {
            $currentTeamPlayerPerformances = [];

            foreach ($currentTeam as $playerSkillVariable) {
                $playerPerformance = $this->createOutputVariable($playerSkillVariable->getKey());
                $this->addLayerFactor($this->createLikelihood($playerSkillVariable, $playerPerformance));
                $currentTeamPlayerPerformances[] = $playerPerformance;
            }

            $this->addOutputVariablesGroup($currentTeamPlayerPerformances);
        }
    }

    /**
     * @return ScheduleSequence<GaussianDistribution>
     */
    public function createPriorSchedule(): ScheduleSequence
    {
        return $this->scheduleSequence(
            array_map(
                static fn (Factor $likelihood): ScheduleStep => new ScheduleStep('Skill to Perf step', $likelihood, 0),
                $this->getLocalFactors()
            ),
            'All skill to performance sending'
        );
    }

    /**
     * @return ScheduleSequence<GaussianDistribution>
     */
    public function createPosteriorSchedule(): ScheduleSequence
    {
        return $this->scheduleSequence(
            array_map(
                static fn (Factor $likelihood): ScheduleStep => new ScheduleStep('Perf to Skill step', $likelihood, 1),
                $this->getLocalFactors()
            ),
            'All performance to skill sending'
        );
    }

    /**
     * @param  KeyedVariable<Player, GaussianDistribution>  $playerSkill
     * @param  KeyedVariable<Player, GaussianDistribution>  $playerPerformance
     */
    private function createLikelihood(KeyedVariable $playerSkill, KeyedVariable $playerPerformance): GaussianLikelihoodFactor
    {
        return new GaussianLikelihoodFactor(
            BasicMath::square($this->getParentFactorGraph()->getGameInfo()->getBeta()),
            $playerPerformance,
            $playerSkill
        );
    }

    /**
     * @return KeyedVariable<Player, GaussianDistribution>
     */
    private function createOutputVariable(Player $player): KeyedVariable
    {
        return $this->getParentFactorGraph()->getVariableFactory()->createKeyedVariable($player, sprintf("%s's performance", $player));
    }
}
