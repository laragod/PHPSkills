<?php

declare(strict_types=1);

namespace Laragod\Skills\FactorGraphs;

use Stringable;

/**
 * A variable in the factor graph that holds a marginal.
 *
 * @template TValue
 */
class Variable implements Stringable
{
    private readonly string $name;

    /** @var TValue */
    private mixed $value;

    /**
     * @param  TValue  $prior
     */
    public function __construct(
        string $name,
        private readonly mixed $prior,
    ) {
        $this->name = 'Variable['.$name.']';
        $this->value = $prior;
    }

    /**
     * @return TValue
     */
    public function getValue(): mixed
    {
        return $this->value;
    }

    /**
     * @param  TValue  $value
     */
    public function setValue(mixed $value): void
    {
        $this->value = $value;
    }

    public function resetToPrior(): void
    {
        $this->value = $this->prior;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
