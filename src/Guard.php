<?php

declare(strict_types=1);

namespace Laragod\Skills;

use InvalidArgumentException;

/**
 * Verifies argument contracts.
 *
 * @see http://www.moserware.com/2008/01/borrowing-ideas-from-3-interesting.html
 *
 * @internal
 */
final class Guard
{
    public static function argumentIsValidIndex(int $index, int $count, string $parameterName): void
    {
        if ($index < 0 || $index >= $count) {
            throw new InvalidArgumentException(sprintf('%s is an invalid index (%d given, %d available)', $parameterName, $index, $count));
        }
    }

    public static function argumentInRangeInclusive(float $value, float $min, float $max, string $parameterName): void
    {
        if ($value < $min || $value > $max) {
            throw new InvalidArgumentException(sprintf('%s is not in the valid range [%s, %s]', $parameterName, $min, $max));
        }
    }
}
