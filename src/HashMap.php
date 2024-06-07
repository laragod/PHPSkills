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
        $hash = self::getHash($key);
        $hashValue = $this->_hashToValue[$hash];

        return $hashValue;
    }

    public function setValue($key, $value): HashMap
    {
        $hash = self::getHash($key);
        $this->_hashToKey[$hash] = $key;
        $this->_hashToValue[$hash] = $value;

        return $this;
    }

    public function getAllKeys(): array
    {
        $keys = array_values($this->_hashToKey);

        return $keys;
    }

    public function getAllValues(): array
    {
        $values = array_values($this->_hashToValue);

        return $values;
    }

    public function count(): int
    {
        return count($this->_hashToKey);
    }

    private static function getHash($key)
    {
        if (is_object($key)) {
            return spl_object_hash($key);
        }

        return $key;
    }
}
