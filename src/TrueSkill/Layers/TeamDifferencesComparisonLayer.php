<?php

declare(strict_types=1);

namespace Laragod\Skills\TrueSkill\Layers;

use Laragod\Skills\FactorGraphs\Variable;
use Laragod\Skills\Numerics\GaussianDistribution;
use Laragod\Skills\TrueSkill\DrawMargin;
use Laragod\Skills\TrueSkill\Factors\GaussianGreaterThanFactor;
use Laragod\Skills\TrueSkill\Factors\GaussianWithinFactor;
use Laragod\Skills\TrueSkill\TrueSkillFactorGraph;

/**
 * Compares every team performance difference against the draw margin.
 *
 * @extends TrueSkillFactorGraphLayer<Variable<GaussianDistribution>, Variable<GaussianDistribution>>
 */
final class TeamDifferencesComparisonLayer extends TrueSkillFactorGraphLayer
{
    private readonly float $epsilon;

    /**
     * @param  list<int>  $teamRanks
     */
    public function __construct(
        TrueSkillFactorGraph $parentGraph,
        private readonly array $teamRanks,
    ) {
        parent::__construct($parentGraph);

        $gameInfo = $this->getParentFactorGraph()->getGameInfo();
        $this->epsilon = DrawMargin::getDrawMarginFromDrawProbability($gameInfo->getDrawProbability(), $gameInfo->getBeta());
    }

    public function buildLayer(): void
    {
        $inputVariablesGroups = $this->getInputVariablesGroups();
        $inputVariablesGroupsCount = count($inputVariablesGroups);

        for ($i = 0; $i < $inputVariablesGroupsCount; $i++) {
            $isDraw = $this->teamRanks[$i] === $this->teamRanks[$i + 1];
            $teamDifference = $inputVariablesGroups[$i][0];

            $this->addLayerFactor(
                $isDraw
                    ? new GaussianWithinFactor($this->epsilon, $teamDifference)
                    : new GaussianGreaterThanFactor($this->epsilon, $teamDifference)
            );
        }
    }
}
