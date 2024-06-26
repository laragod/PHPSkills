<?php

declare(strict_types=1);

namespace Laragod\Skills;

use Exception;

/**
 * Verifies argument contracts.
 *
 * @see http://www.moserware.com/2008/01/borrowing-ideas-from-3-interesting.html
 */
class Guard
{
    public static function argumentNotNull($value, $parameterName): void
    {
        if ($value == null) {
            throw new Exception($parameterName.' can not be null');
        }
    }

    public static function argumentIsValidIndex($index, $count, $parameterName): void
    {
        if (($index < 0) || ($index >= $count)) {
            throw new Exception($parameterName.' is an invalid index');
        }
    }

    public static function argumentInRangeInclusive($value, $min, $max, $parameterName): void
    {
        if (($value < $min) || ($value > $max)) {
            throw new Exception($parameterName.' is not in the valid range ['.$min.', '.$max.']');
        }
    }
}
