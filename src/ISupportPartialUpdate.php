<?php

declare(strict_types=1);

namespace Laragod\Skills;

/**
 * Indicates support for partial updates (where a player only receives part of a skill update).
 */
interface ISupportPartialUpdate
{
    /**
     * How much of a skill update a player should receive, where 0.0 represents no
     * update and 1.0 represents 100% of the update.
     */
    public function getPartialUpdatePercentage(): float;
}
