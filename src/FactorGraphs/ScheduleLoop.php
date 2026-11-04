<?php

declare(strict_types=1);

namespace Laragod\Skills\FactorGraphs;

/**
 * Repeats a schedule until the marginals stop changing by more than a given delta.
 *
 * @template TValue
 *
 * @extends Schedule<TValue>
 */
final class ScheduleLoop extends Schedule
{
    /**
     * @param  Schedule<TValue>  $scheduleToLoop
     */
    public function __construct(
        string $name,
        private readonly Schedule $scheduleToLoop,
        private readonly float $maxDelta,
    ) {
        parent::__construct($name);
    }

    public function visit(): float
    {
        $delta = $this->scheduleToLoop->visit();

        while ($delta > $this->maxDelta) {
            $delta = $this->scheduleToLoop->visit();
        }

        return $delta;
    }
}
