<?php

declare(strict_types=1);

namespace Laragod\Skills\Tests\TrueSkill;

use Laragod\Skills\Tests\TestCase;
use Laragod\Skills\TrueSkill\FactorGraphTrueSkillCalculator;

class FactorGraphTrueSkillCalculatorTest extends TestCase
{
    public function testFactorGraphTrueSkillCalculator()
    {
        $calculator = new FactorGraphTrueSkillCalculator();

        TrueSkillCalculatorTests::testAllTwoPlayerScenarios($this, $calculator);
        TrueSkillCalculatorTests::testAllTwoTeamScenarios($this, $calculator);
        TrueSkillCalculatorTests::testAllMultipleTeamScenarios($this, $calculator);
        TrueSkillCalculatorTests::testPartialPlayScenarios($this, $calculator);
    }
}
