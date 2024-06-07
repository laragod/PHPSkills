<?php

declare(strict_types=1);

namespace Laragod\Skills;

/**
 * Basic hashmap that supports object keys.
 */
class HashMap
{
    private $_hashToValue = [];

    private $_hashToKey = [];

    public function getValue($key)
    {
        $hash = $this->getHash($key);

        return $this->_hashToValue[$hash];
    }

    public function setValue($key, $value): HashMap
    {
        $hash = $this->getHash($key);
        $this->_hashToKey[$hash] = $key;
        $this->_hashToValue[$hash] = $value;

        return $this;
    }

    public function getAllKeys(): array
    {
        return array_values($this->_hashToKey);
    }

    public function getAllValues(): array
    {
        return array_values($this->_hashToValue);
    }

    public function count(): int
    {
        return count($this->_hashToKey);
    }

    private function getHash($key)
    {
        if (is_object($key)) {
            return spl_object_hash($key);
        }

        return $key;
    }
}
