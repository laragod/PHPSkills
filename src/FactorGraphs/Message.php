<?php

declare(strict_types=1);

namespace Laragod\Skills\FactorGraphs;

use Stringable;

/**
 * A message sent from a factor to a variable.
 *
 * @template TValue
 */
final class Message implements Stringable
{
    /**
     * @param  TValue  $value
     */
    public function __construct(
        private mixed $value,
        private readonly string $name,
    ) {}

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

    public function __toString(): string
    {
        return $this->name;
    }
}
