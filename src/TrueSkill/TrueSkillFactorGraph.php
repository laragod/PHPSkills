<?php

declare(strict_types=1);

namespace Laragod\Skills\TrueSkill;

use Laragod\Skills\FactorGraphs\FactorGraph;
use Laragod\Skills\FactorGraphs\FactorGraphLayer;
use Laragod\Skills\FactorGraphs\FactorList;
use Laragod\Skills\FactorGraphs\ScheduleSequence;
use Laragod\Skills\FactorGraphs\Variable;
use Laragod\Skills\FactorGraphs\VariableFactory;
use Laragod\Skills\GameInfo;
use Laragod\Skills\Numerics\GaussianDistribution;
use Laragod\Skills\Rating;
use Laragod\Skills\RatingContainer;
use Laragod\Skills\Team;
use Laragod\Skills\TrueSkill\Layers\IteratedTeamDifferencesInnerLayer;
use Laragod\Skills\TrueSkill\Layers\PlayerPerformancesToTeamPerformancesLayer;
use Laragod\Skills\TrueSkill\Layers\PlayerPriorValuesToSkillsLayer;
use Laragod\Skills\TrueSkill\Layers\PlayerSkillsToPerformancesLayer;
use Laragod\Skills\TrueSkill\Layers\TeamDifferencesComparisonLayer;
use Laragod\Skills\TrueSkill\Layers\TeamPerformancesToTeamPerformanceDifferencesLayer;

/**
 * The full TrueSkill factor graph for any number of teams with any number of players.
 *
 * @extends FactorGraph<GaussianDistribution>
 */
final class TrueSkillFactorGraph extends FactorGraph
{
    private readonly PlayerPriorValuesToSkillsLayer $priorLayer;

    private readonly PlayerSkillsToPerformancesLayer $skillsToPerformancesLayer;

    private readonly PlayerPerformancesToTeamPerformancesLayer $performancesToTeamPerformancesLayer;

    private readonly IteratedTeamDifferencesInnerLayer $teamDifferencesLayer;

    /**
     * @param  list<Team>  $teams  The teams, already sorted by rank.
     * @param  list<int>  $teamRanks  The rank of each team, sorted in non-decreasing order.
     */
    public function __construct(
        private readonly GameInfo $gameInfo,
        array $teams,
        array $teamRanks,
    ) {
        parent::__construct(new VariableFactory(
            static fn (): GaussianDistribution => GaussianDistribution::fromPrecisionMean(0.0, 0.0)
        ));

        $this->priorLayer = new PlayerPriorValuesToSkillsLayer($this, $teams);
        $this->skillsToPerformancesLayer = new PlayerSkillsToPerformancesLayer($this);
        $this->performancesToTeamPerformancesLayer = new PlayerPerformancesToTeamPerformancesLayer($this);
        $this->teamDifferencesLayer = new IteratedTeamDifferencesInnerLayer(
            $this,
            new TeamPerformancesToTeamPerformanceDifferencesLayer($this),
            new TeamDifferencesComparisonLayer($this, $teamRanks)
        );
    }

    public function getGameInfo(): GameInfo
    {
        return $this->gameInfo;
    }

    /**
     * The layers from the priors down to the team comparisons.
     *
     * @return list<FactorGraphLayer<GaussianDistribution, covariant Variable<GaussianDistribution>, covariant Variable<GaussianDistribution>>>
     */
    private function getLayers(): array
    {
        return [
            $this->priorLayer,
            $this->skillsToPerformancesLayer,
            $this->performancesToTeamPerformancesLayer,
            $this->teamDifferencesLayer,
        ];
    }

    public function buildGraph(): void
    {
        $this->priorLayer->buildLayer();

        $this->skillsToPerformancesLayer->setInputVariablesGroups($this->priorLayer->getOutputVariablesGroups());
        $this->skillsToPerformancesLayer->buildLayer();

        $this->performancesToTeamPerformancesLayer->setInputVariablesGroups($this->skillsToPerformancesLayer->getOutputVariablesGroups());
        $this->performancesToTeamPerformancesLayer->buildLayer();

        $this->teamDifferencesLayer->setInputVariablesGroups($this->performancesToTeamPerformancesLayer->getOutputVariablesGroups());
        $this->teamDifferencesLayer->buildLayer();
    }

    public function runSchedule(): void
    {
        $this->createFullSchedule()->visit();
    }

    public function getProbabilityOfRanking(): float
    {
        /** @var FactorList<GaussianDistribution> $factorList */
        $factorList = new FactorList;

        foreach ($this->getLayers() as $currentLayer) {
            foreach ($currentLayer->getLocalFactors() as $currentFactor) {
                $factorList->addFactor($currentFactor);
            }
        }

        return exp($factorList->getLogNormalization());
    }

    /**
     * @return ScheduleSequence<GaussianDistribution>
     */
    private function createFullSchedule(): ScheduleSequence
    {
        $fullSchedule = [];
        $layers = $this->getLayers();

        foreach ($layers as $currentLayer) {
            $currentPriorSchedule = $currentLayer->createPriorSchedule();

            if ($currentPriorSchedule !== null) {
                $fullSchedule[] = $currentPriorSchedule;
            }
        }

        foreach (array_reverse($layers) as $currentLayer) {
            $currentPosteriorSchedule = $currentLayer->createPosteriorSchedule();

            if ($currentPosteriorSchedule !== null) {
                $fullSchedule[] = $currentPosteriorSchedule;
            }
        }

        return new ScheduleSequence('Full schedule', $fullSchedule);
    }

    public function getUpdatedRatings(): RatingContainer
    {
        $result = new RatingContainer;

        foreach ($this->priorLayer->getOutputVariablesGroups() as $currentTeam) {
            foreach ($currentTeam as $currentPlayerSkill) {
                $posterior = $currentPlayerSkill->getValue();

                $result->setRating(
                    $currentPlayerSkill->getKey(),
                    new Rating($posterior->getMean(), $posterior->getStandardDeviation())
                );
            }
        }

        return $result;
    }
}
