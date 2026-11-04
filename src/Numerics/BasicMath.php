<?php

declare(strict_types=1);

namespace Laragod\Skills\Numerics;

/**
 * Basic math functions.
 */
final class BasicMath
{
    /**
     * Squares the input (x^2 = x * x).
     */
    public static function square(float $x): float
    {
        return $x * $x;
    }

    /**
     * Sums the result of applying the callback to every item.
     *
     * @template T
     *
     * @param  array<T>  $items
     * @param  callable(T): float  $callback
     */
    public static function sum(array $items, callable $callback): float
    {
        $sum = 0.0;

        foreach ($items as $item) {
            $sum += $callback($item);
        }

        return $sum;
    }
}
