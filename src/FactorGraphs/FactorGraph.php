<?php

declare(strict_types=1);

namespace Laragod\Skills\FactorGraphs;

abstract class FactorGraph
{
    private $_variableFactory;

    public function getVariableFactory()
    {
        return $this->_variableFactory;
    }

    public function setVariableFactory(VariableFactory $factory): void
    {
        $this->_variableFactory = $factory;
    }

    abstract public function getGameInfo();
}
