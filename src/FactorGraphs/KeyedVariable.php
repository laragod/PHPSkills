<?php

declare(strict_types=1);

namespace Laragod\Skills\FactorGraphs;

/**
 * A variable that remembers the key (for example the player) it was created for.
 *
 * @template TKey
 * @template TValue
 *
 * @extends Variable<TValue>
 */
final class KeyedVariable extends Variable
{
    /**
     * @param  TKey  $key
     * @param  TValue  $prior
     */
    public function __construct(
        private readonly mixed $key,
        string $name,
        mixed $prior,
    ) {
        parent::__construct($name, $prior);
    }

    /**
     * @return TKey
     */
    public function getKey(): mixed
    {
        return $this->key;
    }
}
