<?php

declare(strict_types=1);

namespace Laragod\Skills\TrueSkill\Layers;

use Laragod\Skills\FactorGraphs\Schedule;
use Laragod\Skills\FactorGraphs\ScheduleLoop;
use Laragod\Skills\FactorGraphs\ScheduleSequence;
use Laragod\Skills\FactorGraphs\ScheduleStep;
use Laragod\Skills\FactorGraphs\Variable;
use Laragod\Skills\Numerics\GaussianDistribution;
use Laragod\Skills\TrueSkill\TrueSkillFactorGraph;
use LogicException;

/**
 * Runs the team difference and comparison layers in a loop until they converge.
 *
 * @extends TrueSkillFactorGraphLayer<Variable<GaussianDistribution>, Variable<GaussianDistribution>>
 */
final class IteratedTeamDifferencesInnerLayer extends TrueSkillFactorGraphLayer
{
    private const INITIAL_MAX_DELTA = 0.0001;

    public function __construct(
        TrueSkillFactorGraph $parentGraph,
        private readonly TeamPerformancesToTeamPerformanceDifferencesLayer $teamPerformancesToTeamPerformanceDifferencesLayer,
        private readonly TeamDifferencesComparisonLayer $teamDifferencesComparisonLayer,
    ) {
        parent::__construct($parentGraph);
    }

    public function getLocalFactors(): array
    {
        return [
            ...$this->teamPerformancesToTeamPerformanceDifferencesLayer->getLocalFactors(),
            ...$this->teamDifferencesComparisonLayer->getLocalFactors(),
        ];
    }

    public function buildLayer(): void
    {
        $this->teamPerformancesToTeamPerformanceDifferencesLayer->setInputVariablesGroups($this->getInputVariablesGroups());
        $this->teamPerformancesToTeamPerformanceDifferencesLayer->buildLayer();

        $this->teamDifferencesComparisonLayer->setInputVariablesGroups($this->teamPerformancesToTeamPerformanceDifferencesLayer->getOutputVariablesGroups());
        $this->teamDifferencesComparisonLayer->buildLayer();
    }

    /**
     * @return ScheduleSequence<GaussianDistribution>
     */
    public function createPriorSchedule(): ScheduleSequence
    {
        $teamCount = count($this->getInputVariablesGroups());

        if ($teamCount < 2) {
            throw new LogicException('At least two teams are needed to build the inner schedule');
        }

        $loop = $teamCount === 2
            ? $this->createTwoTeamInnerPriorLoopSchedule()
            : $this->createMultipleTeamInnerPriorLoopSchedule();

        // When dealing with differences, there are always (n-1) differences, so add in the 1
        $differenceFactors = $this->teamPerformancesToTeamPerformanceDifferencesLayer->getLocalFactors();
        $totalTeamDifferences = count($differenceFactors);

        $firstDifferencesFactor = $differenceFactors[0];
        $lastDifferencesFactor = $differenceFactors[$totalTeamDifferences - 1];

        return new ScheduleSequence(
            'inner schedule',
            [
                $loop,
                new ScheduleStep(
                    'teamPerformanceToPerformanceDifferenceFactors[0] @ 1',
                    $firstDifferencesFactor,
                    1
                ),
                new ScheduleStep(
                    sprintf('teamPerformanceToPerformanceDifferenceFactors[teamTeamDifferences = %d - 1] @ 2', $totalTeamDifferences),
                    $lastDifferencesFactor,
                    2
                ),
            ]
        );
    }

    /**
     * @return Schedule<GaussianDistribution>
     */
    private function createTwoTeamInnerPriorLoopSchedule(): Schedule
    {
        $firstPerfToTeamDiff = $this->teamPerformancesToTeamPerformanceDifferencesLayer->getLocalFactors()[0];
        $firstTeamDiffComparison = $this->teamDifferencesComparisonLayer->getLocalFactors()[0];

        return $this->scheduleSequence(
            [
                new ScheduleStep('send team perf to perf differences', $firstPerfToTeamDiff, 0),
                new ScheduleStep('send to greater than or within factor', $firstTeamDiffComparison, 0),
            ],
            'loop of just two teams inner sequence'
        );
    }

    /**
     * @return Schedule<GaussianDistribution>
     */
    private function createMultipleTeamInnerPriorLoopSchedule(): Schedule
    {
        $differenceFactors = $this->teamPerformancesToTeamPerformanceDifferencesLayer->getLocalFactors();
        $comparisonFactors = $this->teamDifferencesComparisonLayer->getLocalFactors();
        $totalTeamDifferences = count($differenceFactors);

        $forwardScheduleList = [];

        for ($i = 0; $i < $totalTeamDifferences - 1; $i++) {
            $forwardScheduleList[] = $this->scheduleSequence(
                [
                    new ScheduleStep(sprintf('team perf to perf diff %d', $i), $differenceFactors[$i], 0),
                    new ScheduleStep(sprintf('greater than or within result factor %d', $i), $comparisonFactors[$i], 0),
                    new ScheduleStep(sprintf('team perf to perf diff factors [%d], 2', $i), $differenceFactors[$i], 2),
                ],
                sprintf('current forward schedule piece %d', $i)
            );
        }

        $forwardSchedule = new ScheduleSequence('forward schedule', $forwardScheduleList);

        $backwardScheduleList = [];

        for ($i = 0; $i < $totalTeamDifferences - 1; $i++) {
            $index = $totalTeamDifferences - 1 - $i;

            $backwardScheduleList[] = new ScheduleSequence(
                'current backward schedule piece',
                [
                    new ScheduleStep(sprintf('teamPerformanceToPerformanceDifferenceFactors[totalTeamDifferences - 1 - %d] @ 0', $i), $differenceFactors[$index], 0),
                    new ScheduleStep(sprintf('greaterThanOrWithinResultFactors[totalTeamDifferences - 1 - %d] @ 0', $i), $comparisonFactors[$index], 0),
                    new ScheduleStep(sprintf('teamPerformanceToPerformanceDifferenceFactors[totalTeamDifferences - 1 - %d] @ 1', $i), $differenceFactors[$index], 1),
                ]
            );
        }

        $backwardSchedule = new ScheduleSequence('backward schedule', $backwardScheduleList);

        $forwardBackwardScheduleToLoop = new ScheduleSequence(
            'forward Backward Schedule To Loop',
            [$forwardSchedule, $backwardSchedule]
        );

        return new ScheduleLoop(
            sprintf('loop with max delta of %f', self::INITIAL_MAX_DELTA),
            $forwardBackwardScheduleToLoop,
            self::INITIAL_MAX_DELTA
        );
    }
}
