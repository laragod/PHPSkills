<?php

declare(strict_types=1);

namespace Laragod\Skills;

use OutOfBoundsException;

/**
 * A small insertion-ordered map that supports object keys.
 *
 * @template TKey of object
 * @template TValue
 */
final class HashMap
{
    /** @var array<int, TKey> */
    private array $keys = [];

    /** @var array<int, TValue> */
    private array $values = [];

    /**
     * @param  TKey  $key
     * @return TValue
     */
    public function getValue(object $key): mixed
    {
        $id = spl_object_id($key);

        if (! array_key_exists($id, $this->values)) {
            throw new OutOfBoundsException(sprintf('No value stored for the given %s key', $key::class));
        }

        return $this->values[$id];
    }

    /**
     * @param  TKey  $key
     */
    public function hasKey(object $key): bool
    {
        return array_key_exists(spl_object_id($key), $this->keys);
    }

    /**
     * @param  TKey  $key
     * @param  TValue  $value
     * @return $this
     */
    public function setValue(object $key, mixed $value): static
    {
        $id = spl_object_id($key);
        $this->keys[$id] = $key;
        $this->values[$id] = $value;

        return $this;
    }

    /**
     * @return list<TKey>
     */
    public function getAllKeys(): array
    {
        return array_values($this->keys);
    }

    /**
     * @return list<TValue>
     */
    public function getAllValues(): array
    {
        return array_values($this->values);
    }

    /**
     * @return int<0, max>
     */
    public function count(): int
    {
        return count($this->keys);
    }
}
