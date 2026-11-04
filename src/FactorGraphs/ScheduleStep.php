<?php

declare(strict_types=1);

namespace Laragod\Skills\FactorGraphs;

/**
 * Updates a single message of a single factor.
 *
 * @template TValue
 *
 * @extends Schedule<TValue>
 */
final class ScheduleStep extends Schedule
{
    /**
     * @param  Factor<TValue>  $factor
     */
    public function __construct(
        string $name,
        private readonly Factor $factor,
        private readonly int $index,
    ) {
        parent::__construct($name);
    }

    public function visit(): float
    {
        return $this->factor->updateMessageIndex($this->index);
    }
}
