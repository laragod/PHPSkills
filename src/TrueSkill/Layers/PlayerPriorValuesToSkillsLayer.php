<?php

declare(strict_types=1);

namespace Laragod\Skills\TrueSkill\Layers;

use Laragod\Skills\FactorGraphs\ScheduleStep;
use Laragod\Skills\FactorGraphs\Variable;
use Laragod\Skills\Numerics\BasicMath;
use Laragod\Skills\Rating;
use Laragod\Skills\TrueSkill\Factors\GaussianPriorFactor;
use Laragod\Skills\TrueSkill\TrueSkillFactorGraph;

// We intentionally have no Posterior schedule since the only purpose here is to
// start the process.
class PlayerPriorValuesToSkillsLayer extends TrueSkillFactorGraphLayer
{
    private array $_teams;

    public function __construct(TrueSkillFactorGraph $parentGraph, array $teams)
    {
        parent::__construct($parentGraph);
        $this->_teams = $teams;
    }

    public function buildLayer(): void
    {
        $teams = $this->_teams;
        foreach ($teams as $currentTeam) {
            $localCurrentTeam = $currentTeam;
            $currentTeamSkills = [];

            $currentTeamAllPlayers = $localCurrentTeam->getAllPlayers();
            foreach ($currentTeamAllPlayers as $currentTeamPlayer) {
                $localCurrentTeamPlayer = $currentTeamPlayer;
                $currentTeamPlayerRating = $currentTeam->getRating($localCurrentTeamPlayer);
                $playerSkill = $this->createSkillOutputVariable($localCurrentTeamPlayer);
                $priorFactor = $this->createPriorFactor($currentTeamPlayerRating, $playerSkill);
                $this->addLayerFactor($priorFactor);
                $currentTeamSkills[] = $playerSkill;
            }

            $outputVariablesGroups = &$this->getOutputVariablesGroups();
            $outputVariablesGroups[] = $currentTeamSkills;
        }
    }

    public function createPriorSchedule() : ?\Laragod\Skills\FactorGraphs\ScheduleSequence
    {
        $localFactors = $this->getLocalFactors();

        return $this->scheduleSequence(
            array_map(
                function ($prior) {
                    return new ScheduleStep('Prior to Skill Step', $prior, 0);
                },
                $localFactors),
            'All priors');
    }

    private function createPriorFactor(Rating $priorRating, Variable $skillsVariable): GaussianPriorFactor
    {
        return new GaussianPriorFactor(
            $priorRating->getMean(),
            BasicMath::square($priorRating->getStandardDeviation()) +
            BasicMath::square($this->getParentFactorGraph()->getGameInfo()->getDynamicsFactor()),
            $skillsVariable
        );
    }

    private function createSkillOutputVariable($key)
    {
        $parentFactorGraph = $this->getParentFactorGraph();
        $variableFactory = $parentFactorGraph->getVariableFactory();

        return $variableFactory->createKeyedVariable($key, $key."'s skill");
    }
}
