<?php

declare(strict_types=1);

namespace Laragod\Skills\Numerics;

// The whole purpose of this class is to make the code for the SkillCalculator(s)
// look a little cleaner

use Exception;

class Range
{
    private $_min;

    private $_max;

    public function __construct($min, $max)
    {
        if ($min > $max) {
            throw new Exception('min > max');
        }

        $this->_min = $min;
        $this->_max = $max;
    }

    public function getMin()
    {
        return $this->_min;
    }

    public function getMax()
    {
        return $this->_max;
    }

    protected static function create($min, $max): Range
    {
        return new Range($min, $max);
    }

    // REVIEW: It's probably bad form to have access statics via a derived class, but the syntax looks better :-)

    public static function inclusive($min, $max)
    {
        return static::create($min, $max);
    }

    public static function exactly($value)
    {
        return static::create($value, $value);
    }

    public static function atLeast($minimumValue)
    {
        return static::create($minimumValue, PHP_INT_MAX);
    }

    public function isInRange($value): bool
    {
        return ($this->_min <= $value) && ($value <= $this->_max);
    }
}
