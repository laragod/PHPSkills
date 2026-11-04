<?php

declare(strict_types=1);

namespace Laragod\Skills\Tests\TrueSkill;

use Laragod\Skills\Tests\TestCase;
use Laragod\Skills\TrueSkill\TwoPlayerTrueSkillCalculator;

class TwoPlayerTrueSkillCalculatorTest extends TestCase
{
    public function test_two_player_true_skill_calculator(): void
    {
        $calculator = new TwoPlayerTrueSkillCalculator;

        // We only support two players
        TrueSkillCalculatorTests::testAllTwoPlayerScenarios($this, $calculator);
    }
}
