<?php

declare(strict_types=1);

namespace Laragod\Skills\TrueSkill\Layers;

use Laragod\Skills\FactorGraphs\Factor;
use Laragod\Skills\FactorGraphs\KeyedVariable;
use Laragod\Skills\FactorGraphs\ScheduleSequence;
use Laragod\Skills\FactorGraphs\ScheduleStep;
use Laragod\Skills\FactorGraphs\Variable;
use Laragod\Skills\Numerics\GaussianDistribution;
use Laragod\Skills\PartialPlay;
use Laragod\Skills\Player;
use Laragod\Skills\TrueSkill\Factors\GaussianWeightedSumFactor;

/**
 * Sums the (partial-play weighted) player performances of every team into a team performance.
 *
 * @extends TrueSkillFactorGraphLayer<KeyedVariable<Player, GaussianDistribution>, Variable<GaussianDistribution>>
 */
final class PlayerPerformancesToTeamPerformancesLayer extends TrueSkillFactorGraphLayer
{
    public function buildLayer(): void
    {
        foreach ($this->getInputVariablesGroups() as $currentTeam) {
            $teamPerformance = $this->createOutputVariable($currentTeam);
            $this->addLayerFactor($this->createPlayerToTeamSumFactor($currentTeam, $teamPerformance));

            // REVIEW: Does it make sense to have groups of one?
            $this->addOutputVariablesGroup([$teamPerformance]);
        }
    }

    /**
     * @return ScheduleSequence<GaussianDistribution>
     */
    public function createPriorSchedule(): ScheduleSequence
    {
        return $this->scheduleSequence(
            array_map(
                static fn (Factor $weightedSumFactor): ScheduleStep => new ScheduleStep('Perf to Team Perf Step', $weightedSumFactor, 0),
                $this->getLocalFactors()
            ),
            'all player perf to team perf schedule'
        );
    }

    /**
     * @return ScheduleSequence<GaussianDistribution>
     */
    public function createPosteriorSchedule(): ScheduleSequence
    {
        $allFactors = [];

        foreach ($this->getLocalFactors() as $currentFactor) {
            $numberOfMessages = $currentFactor->getNumberOfMessages();

            for ($currentIteration = 1; $currentIteration < $numberOfMessages; $currentIteration++) {
                $allFactors[] = new ScheduleStep('team sum perf @'.$currentIteration, $currentFactor, $currentIteration);
            }
        }

        return $this->scheduleSequence($allFactors, "all of the team's sum iterations");
    }

    /**
     * @param  list<KeyedVariable<Player, GaussianDistribution>>  $teamMembers
     * @param  Variable<GaussianDistribution>  $sumVariable
     */
    private function createPlayerToTeamSumFactor(array $teamMembers, Variable $sumVariable): GaussianWeightedSumFactor
    {
        $weights = array_map(
            static fn (KeyedVariable $playerPerformance): float => PartialPlay::getPartialPlayPercentage($playerPerformance->getKey()),
            $teamMembers
        );

        return new GaussianWeightedSumFactor($sumVariable, $teamMembers, $weights);
    }

    /**
     * @param  list<KeyedVariable<Player, GaussianDistribution>>  $team
     * @return Variable<GaussianDistribution>
     */
    private function createOutputVariable(array $team): Variable
    {
        $memberNames = array_map(
            static fn (KeyedVariable $playerPerformance): string => (string) $playerPerformance->getKey(),
            $team
        );

        return $this->getParentFactorGraph()->getVariableFactory()->createBasicVariable(sprintf("Team[%s]'s performance", implode(', ', $memberNames)));
    }
}
