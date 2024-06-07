<?php

declare(strict_types=1);

namespace Laragod\Skills\Elo;

// see http://ratings.fide.com/calculator_rtd.phtml for details
class FideKFactor extends KFactor
{
    public function getValueForRating($rating): int
    {
        if ($rating < 2400) {
            return 15;
        }

        return 10;
    }
}
