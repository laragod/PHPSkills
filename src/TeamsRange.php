<?php

declare(strict_types=1);

namespace Laragod\Skills;

use Laragod\Skills\Numerics\Range;

class TeamsRange extends Range
{
    public function __construct($min, $max)
    {
        parent::__construct($min, $max);
    }

    protected static function create($min, $max): TeamsRange
    {
        return new TeamsRange($min, $max);
    }
}
