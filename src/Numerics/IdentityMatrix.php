<?php

declare(strict_types=1);

namespace Laragod\Skills\Numerics;

class IdentityMatrix extends DiagonalMatrix
{
    public function __construct($rows)
    {
        parent::__construct(array_fill(0, $rows, 1));
    }
}
