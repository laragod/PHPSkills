<?php

declare(strict_types=1);

namespace Laragod\Skills;

/**
 * @internal
 */
final class PartialPlay
{
    /**
     * The smallest percentage that is used, to work around numeric issues near 0.
     */
    private const SMALLEST_PERCENTAGE = 0.0001;

    public static function getPartialPlayPercentage(object $player): float
    {
        // If the player does not support partial play, assume 1.0 == 100%
        if (! $player instanceof ISupportPartialPlay) {
            return 1.0;
        }

        return max($player->getPartialPlayPercentage(), self::SMALLEST_PERCENTAGE);
    }
}
