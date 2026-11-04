<?php

declare(strict_types=1);

namespace Laragod\Skills\TrueSkill\Layers;

use Laragod\Skills\FactorGraphs\FactorGraphLayer;
use Laragod\Skills\FactorGraphs\Variable;
use Laragod\Skills\Numerics\GaussianDistribution;
use Laragod\Skills\TrueSkill\TrueSkillFactorGraph;

/**
 * @template TInputVariable of Variable<GaussianDistribution>
 * @template TOutputVariable of Variable<GaussianDistribution>
 *
 * @extends FactorGraphLayer<GaussianDistribution, TInputVariable, TOutputVariable>
 */
abstract class TrueSkillFactorGraphLayer extends FactorGraphLayer
{
    public function __construct(
        private readonly TrueSkillFactorGraph $parentGraph,
    ) {
        parent::__construct($parentGraph);
    }

    public function getParentFactorGraph(): TrueSkillFactorGraph
    {
        return $this->parentGraph;
    }
}
