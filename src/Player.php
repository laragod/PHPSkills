<?php

declare(strict_types=1);

namespace Laragod\Skills;

use Stringable;

/**
 * Represents a player who has a rating.
 */
final readonly class Player implements ISupportPartialPlay, ISupportPartialUpdate, Stringable
{
    /** 100% play time. */
    public const DEFAULT_PARTIAL_PLAY_PERCENTAGE = 1.0;

    /** 100% of the update. */
    public const DEFAULT_PARTIAL_UPDATE_PERCENTAGE = 1.0;

    /**
     * @param  int|string  $id  The identifier for the player, such as a name or a database id.
     * @param  float  $partialPlayPercentage  The weight (0.0 to 1.0) to give this player when calculating a new rating.
     * @param  float  $partialUpdatePercentage  How much of a skill update (0.0 to 1.0) the player should receive.
     */
    public function __construct(
        private int|string $id,
        private float $partialPlayPercentage = self::DEFAULT_PARTIAL_PLAY_PERCENTAGE,
        private float $partialUpdatePercentage = self::DEFAULT_PARTIAL_UPDATE_PERCENTAGE,
    ) {
        Guard::argumentInRangeInclusive($partialPlayPercentage, 0.0, 1.0, 'partialPlayPercentage');
        Guard::argumentInRangeInclusive($partialUpdatePercentage, 0.0, 1.0, 'partialUpdatePercentage');
    }

    /**
     * The identifier for the player, such as a name or a database id.
     */
    public function getId(): int|string
    {
        return $this->id;
    }

    public function getPartialPlayPercentage(): float
    {
        return $this->partialPlayPercentage;
    }

    public function getPartialUpdatePercentage(): float
    {
        return $this->partialUpdatePercentage;
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }
}
