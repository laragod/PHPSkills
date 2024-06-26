<?php

declare(strict_types=1);

namespace Laragod\Skills\TrueSkill\Layers;

use Laragod\Skills\FactorGraphs\KeyedVariable;
use Laragod\Skills\FactorGraphs\ScheduleStep;
use Laragod\Skills\Numerics\BasicMath;
use Laragod\Skills\TrueSkill\Factors\GaussianLikelihoodFactor;
use Laragod\Skills\TrueSkill\TrueSkillFactorGraph;

class PlayerSkillsToPerformancesLayer extends TrueSkillFactorGraphLayer
{
    public function __construct(TrueSkillFactorGraph $parentGraph)
    {
        parent::__construct($parentGraph);
    }

    public function buildLayer(): void
    {
        $inputVariablesGroups = $this->getInputVariablesGroups();
        $outputVariablesGroups = &$this->getOutputVariablesGroups();

        foreach ($inputVariablesGroups as $currentTeam) {
            $currentTeamPlayerPerformances = [];

            foreach ($currentTeam as $playerSkillVariable) {
                $localPlayerSkillVariable = $playerSkillVariable;
                $currentPlayer = $localPlayerSkillVariable->getKey();
                $playerPerformance = $this->createOutputVariable($currentPlayer);
                $newLikelihoodFactor = $this->createLikelihood($localPlayerSkillVariable, $playerPerformance);
                $this->addLayerFactor($newLikelihoodFactor);
                $currentTeamPlayerPerformances[] = $playerPerformance;
            }

            $outputVariablesGroups[] = $currentTeamPlayerPerformances;
        }
    }

    private function createLikelihood(KeyedVariable $playerSkill, KeyedVariable $playerPerformance): GaussianLikelihoodFactor
    {
        return new GaussianLikelihoodFactor(
            BasicMath::square($this->getParentFactorGraph()->getGameInfo()->getBeta()),
            $playerPerformance,
            $playerSkill
        );
    }

    private function createOutputVariable($key)
    {
        return $this->getParentFactorGraph()->getVariableFactory()->createKeyedVariable($key, $key."'s performance");
    }

    public function createPriorSchedule() : ?\Laragod\Skills\FactorGraphs\ScheduleSequence
    {
        $localFactors = $this->getLocalFactors();

        return $this->scheduleSequence(
            array_map(
                function ($likelihood) {
                    return new ScheduleStep('Skill to Perf step', $likelihood, 0);
                },
                $localFactors),
            'All skill to performance sending');
    }

    public function createPosteriorSchedule() : ?\Laragod\Skills\FactorGraphs\ScheduleSequence
    {
        $localFactors = $this->getLocalFactors();

        return $this->scheduleSequence(
            array_map(
                function ($likelihood) {
                    return new ScheduleStep('name', $likelihood, 1);
                },
                $localFactors),
            'All skill to performance sending');
    }
}
