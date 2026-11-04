<?php

declare(strict_types=1);

namespace Laragod\Skills\FactorGraphs;

use Closure;

/**
 * Creates variables that start out with a fresh prior.
 *
 * @template TValue
 */
final readonly class VariableFactory
{
    /**
     * @param  Closure(): TValue  $variablePriorInitializer
     */
    public function __construct(
        private Closure $variablePriorInitializer,
    ) {}

    /**
     * @return Variable<TValue>
     */
    public function createBasicVariable(string $name): Variable
    {
        return new Variable($name, ($this->variablePriorInitializer)());
    }

    /**
     * @template TKey
     *
     * @param  TKey  $key
     * @return KeyedVariable<TKey, TValue>
     */
    public function createKeyedVariable(mixed $key, string $name): KeyedVariable
    {
        return new KeyedVariable($key, $name, ($this->variablePriorInitializer)());
    }
}
