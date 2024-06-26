<?php

declare(strict_types=1);

namespace Laragod\Skills;

interface ISupportPartialUpdate
{
    /**
     * Indicated how much of a skill update a player should receive where 0.0 represents no update and 1.0 represents 100% of the update.
     */
    public function getPartialUpdatePercentage();
}
