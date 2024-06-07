<?php

declare(strict_types=1);

namespace Laragod\Skills\Elo;

use Laragod\Skills\Rating;

/**
 * An Elo rating represented by a single number (mean).
 */
class EloRating extends Rating
{
    public function __construct($rating)
    {
        parent::__construct($rating, 0);
    }
}
