<?php

declare(strict_types=1);

namespace Laragod\Skills\TrueSkill\Layers;

use Laragod\Skills\FactorGraphs\Variable;
use Laragod\Skills\Numerics\GaussianDistribution;
use Laragod\Skills\TrueSkill\Factors\GaussianWeightedSumFactor;

/**
 * Turns the performances of adjacent teams into performance differences.
 *
 * @extends TrueSkillFactorGraphLayer<Variable<GaussianDistribution>, Variable<GaussianDistribution>>
 */
final class TeamPerformancesToTeamPerformanceDifferencesLayer extends TrueSkillFactorGraphLayer
{
    public function buildLayer(): void
    {
        $inputVariablesGroups = $this->getInputVariablesGroups();
        $inputVariablesGroupsCount = count($inputVariablesGroups);

        for ($i = 0; $i < $inputVariablesGroupsCount - 1; $i++) {
            $strongerTeam = $inputVariablesGroups[$i][0];
            $weakerTeam = $inputVariablesGroups[$i + 1][0];

            $currentDifference = $this->createOutputVariable();
            $this->addLayerFactor($this->createTeamPerformanceToDifferenceFactor($strongerTeam, $weakerTeam, $currentDifference));

            // REVIEW: Does it make sense to have groups of one?
            $this->addOutputVariablesGroup([$currentDifference]);
        }
    }

    /**
     * @param  Variable<GaussianDistribution>  $strongerTeam
     * @param  Variable<GaussianDistribution>  $weakerTeam
     * @param  Variable<GaussianDistribution>  $output
     */
    private function createTeamPerformanceToDifferenceFactor(Variable $strongerTeam, Variable $weakerTeam, Variable $output): GaussianWeightedSumFactor
    {
        return new GaussianWeightedSumFactor($output, [$strongerTeam, $weakerTeam], [1.0, -1.0]);
    }

    /**
     * @return Variable<GaussianDistribution>
     */
    private function createOutputVariable(): Variable
    {
        return $this->getParentFactorGraph()->getVariableFactory()->createBasicVariable('Team performance difference');
    }
}
