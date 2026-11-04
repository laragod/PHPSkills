<?php

declare(strict_types=1);

namespace Laragod\Skills\FactorGraphs;

/**
 * @template TValue
 */
abstract class FactorGraph
{
    /**
     * @param  VariableFactory<TValue>  $variableFactory
     */
    protected function __construct(
        private readonly VariableFactory $variableFactory,
    ) {}

    /**
     * @return VariableFactory<TValue>
     */
    public function getVariableFactory(): VariableFactory
    {
        return $this->variableFactory;
    }
}
