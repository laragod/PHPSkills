<?php

declare(strict_types=1);

namespace Laragod\Skills;

class Teams
{
    public static function concat(/*variable arguments*/): array
    {
        $args = func_get_args();
        $result = [];

        foreach ($args as $currentTeam) {
            $localCurrentTeam = $currentTeam;
            $result[] = $localCurrentTeam;
        }

        return $result;
    }
}
