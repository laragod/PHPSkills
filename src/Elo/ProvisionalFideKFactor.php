<?php

declare(strict_types=1);

namespace Laragod\Skills\Elo;

/**
 * Indicates someone who has played less than 30 games.
 */
class ProvisionalFideKFactor extends FideKFactor
{
    public function getValueForRating($rating): int
    {
        return 25;
    }
}
