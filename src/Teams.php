<?php

declare(strict_types=1);

namespace Laragod\Skills;

class Teams
{
    public static function concat(...$args/*variable arguments*/): array
    {
        $result = [];

        foreach ($args as $currentTeam) {
            $localCurrentTeam = $currentTeam;
            $result[] = $localCurrentTeam;
        }

        return $result;
    }
}
