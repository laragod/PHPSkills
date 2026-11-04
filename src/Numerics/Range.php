<?php

declare(strict_types=1);

namespace Laragod\Skills\Numerics;

use InvalidArgumentException;
use Stringable;

/**
 * An inclusive integer range, used to describe what a SkillCalculator supports.
 *
 * @phpstan-consistent-constructor
 */
class Range implements Stringable
{
    final public function __construct(
        private readonly int $min,
        private readonly int $max,
    ) {
        if ($min > $max) {
            throw new InvalidArgumentException(sprintf('min (%d) must not be greater than max (%d)', $min, $max));
        }
    }

    public function getMin(): int
    {
        return $this->min;
    }

    public function getMax(): int
    {
        return $this->max;
    }

    public static function inclusive(int $min, int $max): static
    {
        return new static($min, $max);
    }

    public static function exactly(int $value): static
    {
        return new static($value, $value);
    }

    public static function atLeast(int $minimumValue): static
    {
        return new static($minimumValue, PHP_INT_MAX);
    }

    public function isInRange(int $value): bool
    {
        return $this->min <= $value && $value <= $this->max;
    }

    public function __toString(): string
    {
        if ($this->min === $this->max) {
            return sprintf('exactly %d', $this->min);
        }

        if ($this->max === PHP_INT_MAX) {
            return sprintf('at least %d', $this->min);
        }

        return sprintf('%d to %d', $this->min, $this->max);
    }
}
