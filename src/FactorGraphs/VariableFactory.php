<?php

declare(strict_types=1);

namespace Laragod\Skills\FactorGraphs;

class VariableFactory
{
    // using a Func<TValue> to encourage fresh copies in case it's overwritten
    private $_variablePriorInitializer;

    public function __construct($variablePriorInitializer)
    {
        $this->_variablePriorInitializer = $variablePriorInitializer;
    }

    public function createBasicVariable($name): Variable
    {
        $initializer = $this->_variablePriorInitializer;

        return new Variable($name, $initializer());
    }

    public function createKeyedVariable($key, $name): KeyedVariable
    {
        $initializer = $this->_variablePriorInitializer;

        return new KeyedVariable($key, $name, $initializer());
    }
}
