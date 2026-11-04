<?php

declare(strict_types=1);

namespace Laragod\Skills\Numerics;

use InvalidArgumentException;

final class SquareMatrix extends Matrix
{
    /**
     * @param  float  ...$allValues  The values in row-major order; the count must be a perfect square.
     */
    public function __construct(float ...$allValues)
    {
        $allValues = array_values($allValues);
        $rows = (int) sqrt(count($allValues));

        if ($rows * $rows !== count($allValues)) {
            throw new InvalidArgumentException(sprintf('%d values cannot form a square matrix', count($allValues)));
        }

        $matrixData = [];
        $allValuesIndex = 0;

        for ($currentRow = 0; $currentRow < $rows; $currentRow++) {
            for ($currentColumn = 0; $currentColumn < $rows; $currentColumn++) {
                $matrixData[$currentRow][$currentColumn] = $allValues[$allValuesIndex++];
            }
        }

        parent::__construct($rows, $rows, $matrixData);
    }
}
