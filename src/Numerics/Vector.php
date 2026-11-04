<?php

declare(strict_types=1);

namespace Laragod\Skills\Numerics;

/**
 * A column vector (an n x 1 matrix).
 */
final class Vector extends Matrix
{
    /**
     * @param  list<float>  $vectorValues
     */
    public function __construct(array $vectorValues)
    {
        $columnValues = [];

        foreach ($vectorValues as $currentVectorValue) {
            $columnValues[] = [$currentVectorValue];
        }

        parent::__construct(count($vectorValues), 1, $columnValues);
    }
}
