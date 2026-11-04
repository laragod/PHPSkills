<?php

declare(strict_types=1);

namespace Laragod\Skills\Elo;

/**
 * The K-factor controls how much a single game can change an Elo rating.
 */
class KFactor
{
    public const DEFAULT_KFACTOR = 24.0;

    public function __construct(
        private readonly float $value = self::DEFAULT_KFACTOR,
    ) {}

    public function getValueForRating(float $rating): float
    {
        return $this->value;
    }
}
