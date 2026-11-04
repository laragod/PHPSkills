<?php

declare(strict_types=1);

namespace Laragod\Skills\Tests\TrueSkill;

use Laragod\Skills\Tests\TestCase;
use Laragod\Skills\TrueSkill\TwoTeamTrueSkillCalculator;

class TwoTeamTrueSkillCalculatorTest extends TestCase
{
    public function test_two_team_true_skill_calculator(): void
    {
        $calculator = new TwoTeamTrueSkillCalculator;

        // We only support two players
        TrueSkillCalculatorTests::testAllTwoPlayerScenarios($this, $calculator);
        TrueSkillCalculatorTests::testAllTwoTeamScenarios($this, $calculator);
    }
}
