<?php

declare(strict_types=1);

namespace Laragod\Skills\TrueSkill\Layers;

use Laragod\Skills\FactorGraphs\Variable;
use Laragod\Skills\TrueSkill\Factors\GaussianWeightedSumFactor;
use Laragod\Skills\TrueSkill\TrueSkillFactorGraph;

class TeamPerformancesToTeamPerformanceDifferencesLayer extends TrueSkillFactorGraphLayer
{
    public function __construct(TrueSkillFactorGraph $parentGraph)
    {
        parent::__construct($parentGraph);
    }

    public function buildLayer(): void
    {
        $inputVariablesGroups = $this->getInputVariablesGroups();
        $inputVariablesGroupsCount = count($inputVariablesGroups);
        $outputVariablesGroup = &$this->getOutputVariablesGroups();

        for ($i = 0; $i < $inputVariablesGroupsCount - 1; $i++) {
            $strongerTeam = $inputVariablesGroups[$i][0];
            $weakerTeam = $inputVariablesGroups[$i + 1][0];

            $currentDifference = $this->createOutputVariable();
            $newDifferencesFactor = $this->createTeamPerformanceToDifferenceFactor($strongerTeam, $weakerTeam, $currentDifference);
            $this->addLayerFactor($newDifferencesFactor);

            // REVIEW: Does it make sense to have groups of one?
            $outputVariablesGroup[] = [$currentDifference];
        }
    }

    private function createTeamPerformanceToDifferenceFactor(Variable $strongerTeam,
        Variable $weakerTeam,
        Variable $output): GaussianWeightedSumFactor
    {
        $teams = [$strongerTeam, $weakerTeam];
        $weights = [1.0, -1.0];

        return new GaussianWeightedSumFactor($output, $teams, $weights);
    }

    private function createOutputVariable()
    {
        return $this->getParentFactorGraph()->getVariableFactory()->createBasicVariable('Team performance difference');
    }
}
