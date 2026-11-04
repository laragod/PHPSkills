<?php

declare(strict_types=1);

namespace Laragod\Skills\TrueSkill\Factors;

use Laragod\Skills\FactorGraphs\Factor;
use Laragod\Skills\FactorGraphs\Message;
use Laragod\Skills\FactorGraphs\Variable;
use Laragod\Skills\Numerics\GaussianDistribution;

/**
 * @extends Factor<GaussianDistribution>
 */
abstract class GaussianFactor extends Factor
{
    /**
     * Sends the factor-graph message and returns the log-normalization constant.
     *
     * @param  Message<GaussianDistribution>  $message
     * @param  Variable<GaussianDistribution>  $variable
     */
    protected function sendMessageVariable(Message $message, Variable $variable): float
    {
        $marginal = $variable->getValue();
        $messageValue = $message->getValue();
        $logZ = GaussianDistribution::logProductNormalization($marginal, $messageValue);
        $variable->setValue(GaussianDistribution::multiply($marginal, $messageValue));

        return $logZ;
    }

    /**
     * @param  Variable<GaussianDistribution>  $variable
     * @return Message<GaussianDistribution>
     */
    public function createVariableToMessageBinding(Variable $variable): Message
    {
        return $this->createVariableToMessageBindingWithMessage(
            $variable,
            new Message(
                GaussianDistribution::fromPrecisionMean(0.0, 0.0),
                sprintf('message from %s to %s', $this, $variable)
            )
        );
    }
}
