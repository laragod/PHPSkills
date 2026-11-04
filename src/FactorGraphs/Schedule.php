<?php

declare(strict_types=1);

namespace Laragod\Skills\FactorGraphs;

use Stringable;

/**
 * Describes the order in which factors update their messages.
 *
 * @template TValue
 */
abstract class Schedule implements Stringable
{
    protected function __construct(
        private readonly string $name,
    ) {}

    /**
     * Runs the schedule.
     *
     * @return float The largest change in any marginal that the schedule caused.
     */
    abstract public function visit(): float;

    public function __toString(): string
    {
        return $this->name;
    }
}
