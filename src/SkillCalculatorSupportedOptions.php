<?php

declare(strict_types=1);

namespace Laragod\Skills;

/**
 * Bit flags describing the optional features a SkillCalculator supports.
 */
final class SkillCalculatorSupportedOptions
{
    public const NONE = 0x00;

    public const PARTIAL_PLAY = 0x01;

    public const PARTIAL_UPDATE = 0x02;
}
