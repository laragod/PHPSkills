<?php

declare(strict_types=1);

namespace Laragod\Skills\FactorGraphs;

class ScheduleStep extends Schedule
{
    private \Laragod\Skills\FactorGraphs\Factor $_factor;

    private $_index;

    public function __construct($name, Factor $factor, $index)
    {
        parent::__construct($name);
        $this->_factor = $factor;
        $this->_index = $index;
    }

    public function visit($depth = -1, $maxDepth = 0)
    {
        $currentFactor = $this->_factor;

        return $currentFactor->updateMessageIndex($this->_index);
    }
}
