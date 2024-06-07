<?php

declare(strict_types=1);

namespace Laragod\Skills\Elo;

class KFactor
{
    const DEFAULT_KFACTOR = 24;

    private $_value;

    public function __construct($exactKFactor = self::DEFAULT_KFACTOR)
    {
        $this->_value = $exactKFactor;
    }

    public function getValueForRating($rating)
    {
        return $this->_value;
    }
}
