<?php

declare(strict_types=1);

namespace Laragod\Skills\Numerics;

class DiagonalMatrix extends Matrix
{
    /**
     * @param  list<float>  $diagonalValues
     */
    public function __construct(array $diagonalValues)
    {
        $size = count($diagonalValues);
        $matrixData = [];

        for ($currentRow = 0; $currentRow < $size; $currentRow++) {
            for ($currentCol = 0; $currentCol < $size; $currentCol++) {
                $matrixData[$currentRow][$currentCol] = $currentRow === $currentCol ? $diagonalValues[$currentRow] : 0.0;
            }
        }

        parent::__construct($size, $size, $matrixData);
    }
}
