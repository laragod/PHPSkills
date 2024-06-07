<?php

declare(strict_types=1);

namespace Laragod\Skills\FactorGraphs;

class Variable
{
    private string $_name;

    private $_prior;

    private $_value;

    public function __construct($name, $prior)
    {
        $this->_name = 'Variable['.$name.']';
        $this->_prior = $prior;
        $this->resetToPrior();
    }

    public function getValue()
    {
        return $this->_value;
    }

    public function setValue($value): void
    {
        $this->_value = $value;
    }

    public function resetToPrior(): void
    {
        $this->_value = $this->_prior;
    }

    public function __toString()
    {
        return $this->_name;
    }
}
