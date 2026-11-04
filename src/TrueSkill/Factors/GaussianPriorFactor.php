<?php

declare(strict_types=1);

namespace Laragod\Skills\TrueSkill\Factors;

use Laragod\Skills\FactorGraphs\Message;
use Laragod\Skills\FactorGraphs\Variable;
use Laragod\Skills\Numerics\GaussianDistribution;

/**
 * Supplies the factor graph with prior information.
 *
 * See the accompanying math paper for more details.
 */
final class GaussianPriorFactor extends GaussianFactor
{
    private readonly GaussianDistribution $newMessage;

    /**
     * @param  Variable<GaussianDistribution>  $variable
     */
    public function __construct(float $mean, float $variance, Variable $variable)
    {
        parent::__construct(sprintf('Prior value going to %s', $variable));
        $this->newMessage = new GaussianDistribution($mean, sqrt($variance));

        $this->createVariableToMessageBindingWithMessage(
            $variable,
            new Message(
                GaussianDistribution::fromPrecisionMean(0.0, 0.0),
                sprintf('message from %s to %s', $this, $variable)
            )
        );
    }

    /**
     * @param  Message<GaussianDistribution>  $message
     * @param  Variable<GaussianDistribution>  $variable
     */
    protected function updateMessageVariable(Message $message, Variable $variable): float
    {
        $oldMarginal = $variable->getValue();
        $oldMessage = $message->getValue();

        $newMarginal = GaussianDistribution::fromPrecisionMean(
            $oldMarginal->getPrecisionMean() + $this->newMessage->getPrecisionMean() - $oldMessage->getPrecisionMean(),
            $oldMarginal->getPrecision() + $this->newMessage->getPrecision() - $oldMessage->getPrecision()
        );

        $variable->setValue($newMarginal);
        $message->setValue($this->newMessage);

        return GaussianDistribution::absoluteDifference($oldMarginal, $newMarginal);
    }
}
