<?php

declare(strict_types=1);

namespace Laragod\Skills;

use Laragod\Skills\Numerics\Range;

class PlayersRange extends Range
{
    public function __construct($min, $max)
    {
        parent::__construct($min, $max);
    }

    protected static function create($min, $max): PlayersRange
    {
        return new PlayersRange($min, $max);
    }
}
