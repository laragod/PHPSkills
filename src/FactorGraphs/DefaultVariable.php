<?php

declare(strict_types=1);

namespace Laragod\Skills\FactorGraphs;

use Exception;

// XXX: This class is not used anywhere
class DefaultVariable extends Variable
{
    public function __construct()
    {
        parent::__construct('Default', null);
    }

    public function getValue() : null
    {
        return null;
    }

    public function setValue($value) : void
    {
        throw new Exception();
    }
}
