<?php

declare(strict_types=1);

namespace Laragod\Skills\Elo;

use Laragod\Skills\Rating;

/**
 * An Elo rating is represented by a single number (the mean); it has no uncertainty.
 */
final class EloRating extends Rating
{
    public function __construct(float $rating)
    {
        parent::__construct($rating, 0.0);
    }
}
