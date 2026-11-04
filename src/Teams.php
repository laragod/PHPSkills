<?php

declare(strict_types=1);

namespace Laragod\Skills;

/**
 * Helper for building the list of teams a SkillCalculator expects.
 */
final class Teams
{
    /**
     * @return list<Team>
     */
    public static function concat(Team ...$teams): array
    {
        return array_values($teams);
    }
}
