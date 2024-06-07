<?php

declare(strict_types=1);

namespace Laragod\Skills\FactorGraphs;

// edit this
abstract class FactorGraphLayer
{
    private $_localFactors = [];

    private $_outputVariablesGroups = [];

    private $_inputVariablesGroups = [];

    private \Laragod\Skills\FactorGraphs\FactorGraph $_parentFactorGraph;

    protected function __construct(FactorGraph $parentGraph)
    {
        $this->_parentFactorGraph = $parentGraph;
    }

    protected function getInputVariablesGroups(): array
    {
        return $this->_inputVariablesGroups;
    }

    // HACK

    public function getParentFactorGraph(): FactorGraph
    {
        return $this->_parentFactorGraph;
    }

    /**
     * This reference is still needed
     */
    public function &getOutputVariablesGroups(): array
    {
        return $this->_outputVariablesGroups;
    }

    public function getLocalFactors(): array
    {
        return $this->_localFactors;
    }

    public function setInputVariablesGroups($value): void
    {
        $this->_inputVariablesGroups = $value;
    }

    protected function scheduleSequence(array $itemsToSequence, $name): ScheduleSequence
    {
        return new ScheduleSequence($name, $itemsToSequence);
    }

    protected function addLayerFactor(Factor $factor) : void
    {
        $this->_localFactors[] = $factor;
    }

    abstract public function buildLayer();

    public function createPriorSchedule() : ?ScheduleSequence
    {
        return null;
    }

    public function createPosteriorSchedule() : ?ScheduleSequence
    {
        return null;
    }
}
