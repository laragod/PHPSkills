<?php

declare(strict_types=1);

namespace Laragod\Skills\Numerics;

class SquareMatrix extends Matrix
{
    public function __construct(...$allValues)
    {
        $rows = (int) sqrt(count($allValues));
        $cols = $rows;

        $matrixData = [];
        $allValuesIndex = 0;

        for ($currentRow = 0; $currentRow < $rows; $currentRow++) {
            for ($currentColumn = 0; $currentColumn < $cols; $currentColumn++) {
                $matrixData[$currentRow][$currentColumn] = $allValues[$allValuesIndex++];
            }
        }

        parent::__construct($rows, $cols, $matrixData);
    }
}
