<?php

declare(strict_types=1);

namespace Laragod\Skills\FactorGraphs;

abstract class Schedule
{
    private $_name;

    protected function __construct($name)
    {
        $this->_name = $name;
    }

    abstract public function visit($depth = -1, $maxDepth = 0);

    public function __toString()
    {
        return $this->_name;
    }
}
