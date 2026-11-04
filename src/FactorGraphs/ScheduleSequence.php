<?php

declare(strict_types=1);

namespace Laragod\Skills\FactorGraphs;

/**
 * Runs its child schedules one after another.
 *
 * @template TValue
 *
 * @extends Schedule<TValue>
 */
final class ScheduleSequence extends Schedule
{
    /**
     * @param  list<Schedule<TValue>>  $schedules
     */
    public function __construct(
        string $name,
        private readonly array $schedules,
    ) {
        parent::__construct($name);
    }

    public function visit(): float
    {
        $maxDelta = 0.0;

        foreach ($this->schedules as $schedule) {
            $maxDelta = max($schedule->visit(), $maxDelta);
        }

        return $maxDelta;
    }
}
