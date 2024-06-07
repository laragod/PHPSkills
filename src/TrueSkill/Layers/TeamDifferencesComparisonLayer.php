<?php

declare(strict_types=1);

namespace Laragod\Skills\TrueSkill\Layers;

use Laragod\Skills\TrueSkill\DrawMargin;
use Laragod\Skills\TrueSkill\Factors\GaussianGreaterThanFactor;
use Laragod\Skills\TrueSkill\Factors\GaussianWithinFactor;
use Laragod\Skills\TrueSkill\TrueSkillFactorGraph;

class TeamDifferencesComparisonLayer extends TrueSkillFactorGraphLayer
{
    private float|int $_epsilon;

    private array $_teamRanks;

    public function __construct(TrueSkillFactorGraph $parentGraph, array $teamRanks)
    {
        parent::__construct($parentGraph);
        $this->_teamRanks = $teamRanks;
        $gameInfo = $this->getParentFactorGraph()->getGameInfo();
        $this->_epsilon = DrawMargin::getDrawMarginFromDrawProbability($gameInfo->getDrawProbability(), $gameInfo->getBeta());
    }

    public function buildLayer(): void
    {
        $inputVarGroups = $this->getInputVariablesGroups();
        $inputVarGroupsCount = count($inputVarGroups);

        for ($i = 0; $i < $inputVarGroupsCount; $i++) {
            $isDraw = ($this->_teamRanks[$i] == $this->_teamRanks[$i + 1]);
            $teamDifference = $inputVarGroups[$i][0];

            $factor =
                $isDraw
                    ? new GaussianWithinFactor($this->_epsilon, $teamDifference)
                    : new GaussianGreaterThanFactor($this->_epsilon, $teamDifference);

            $this->addLayerFactor($factor);
        }
    }
}
