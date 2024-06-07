<?php

declare(strict_types=1);

namespace Laragod\Skills\FactorGraphs;

class KeyedVariable extends Variable
{
    private $_key;

    public function __construct($key, $name, $prior)
    {
        parent::__construct($name, $prior);
        $this->_key = $key;
    }

    public function getKey()
    {
        return $this->_key;
    }
}
