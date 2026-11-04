<?php

declare(strict_types=1);

namespace Laragod\Skills\FactorGraphs;

/**
 * A layer of the factor graph: it turns groups of input variables into groups of
 * output variables, creating the factors that connect them along the way.
 *
 * @template TValue
 * @template TInputVariable of Variable<TValue>
 * @template TOutputVariable of Variable<TValue>
 */
abstract class FactorGraphLayer
{
    /** @var list<Factor<TValue>> */
    private array $localFactors = [];

    /** @var list<list<TInputVariable>> */
    private array $inputVariablesGroups = [];

    /** @var list<list<TOutputVariable>> */
    private array $outputVariablesGroups = [];

    /**
     * @param  FactorGraph<TValue>  $parentFactorGraph
     */
    protected function __construct(
        private readonly FactorGraph $parentFactorGraph,
    ) {}

    /**
     * @return FactorGraph<TValue>
     */
    public function getParentFactorGraph(): FactorGraph
    {
        return $this->parentFactorGraph;
    }

    /**
     * @return list<list<TInputVariable>>
     */
    protected function getInputVariablesGroups(): array
    {
        return $this->inputVariablesGroups;
    }

    /**
     * @param  list<list<TInputVariable>>  $inputVariablesGroups
     */
    public function setInputVariablesGroups(array $inputVariablesGroups): void
    {
        $this->inputVariablesGroups = $inputVariablesGroups;
    }

    /**
     * @return list<list<TOutputVariable>>
     */
    public function getOutputVariablesGroups(): array
    {
        return $this->outputVariablesGroups;
    }

    /**
     * @param  list<TOutputVariable>  $group
     */
    protected function addOutputVariablesGroup(array $group): void
    {
        $this->outputVariablesGroups[] = $group;
    }

    /**
     * @return list<Factor<TValue>>
     */
    public function getLocalFactors(): array
    {
        return $this->localFactors;
    }

    /**
     * @param  Factor<TValue>  $factor
     */
    protected function addLayerFactor(Factor $factor): void
    {
        $this->localFactors[] = $factor;
    }

    /**
     * @param  list<Schedule<TValue>>  $itemsToSequence
     * @return ScheduleSequence<TValue>
     */
    protected function scheduleSequence(array $itemsToSequence, string $name): ScheduleSequence
    {
        return new ScheduleSequence($name, $itemsToSequence);
    }

    abstract public function buildLayer(): void;

    /**
     * @return Schedule<TValue>|null
     */
    public function createPriorSchedule(): ?Schedule
    {
        return null;
    }

    /**
     * @return Schedule<TValue>|null
     */
    public function createPosteriorSchedule(): ?Schedule
    {
        return null;
    }
}
