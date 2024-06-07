<?php

declare(strict_types=1);

namespace Laragod\Skills\FactorGraphs;

class ScheduleSequence extends Schedule
{
    private array $_schedules;

    public function __construct($name, array $schedules)
    {
        parent::__construct($name);
        $this->_schedules = $schedules;
    }

    public function visit($depth = -1, $maxDepth = 0)
    {
        $maxDelta = 0;

        $schedules = $this->_schedules;
        foreach ($schedules as $currentSchedule) {
            $currentVisit = $currentSchedule->visit($depth + 1, $maxDepth);
            $maxDelta = max($currentVisit, $maxDelta);
        }

        return $maxDelta;
    }
}
