<?php

declare(strict_types=1);

namespace Laragod\Skills\TrueSkill\Layers;

use Laragod\Skills\FactorGraphs\FactorGraphLayer;
use Laragod\Skills\TrueSkill\TrueSkillFactorGraph;

abstract class TrueSkillFactorGraphLayer extends FactorGraphLayer
{
    public function __construct(TrueSkillFactorGraph $parentGraph)
    {
        parent::__construct($parentGraph);
    }
}
