<?php

declare(strict_types=1);

namespace Laragod\Skills;

/**
 * Indicates support for partial play (where a player only plays part of the time).
 */
interface ISupportPartialPlay
{
    /**
     * The percentage of time the player should be weighted, where 0.0 indicates the
     * player did not play and 1.0 indicates the player played 100% of the time.
     */
    public function getPartialPlayPercentage(): float;
}
