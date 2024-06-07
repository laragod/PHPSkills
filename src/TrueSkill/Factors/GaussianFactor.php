<?php

declare(strict_types=1);

namespace Laragod\Skills\TrueSkill\Factors;

use Laragod\Skills\FactorGraphs\Factor;
use Laragod\Skills\FactorGraphs\Message;
use Laragod\Skills\FactorGraphs\Variable;
use Laragod\Skills\Numerics\GaussianDistribution;

abstract class GaussianFactor extends Factor
{
    protected function __construct($name)
    {
        parent::__construct($name);
    }

    /**
     * Sends the factor-graph message with and returns the log-normalization constant.
     *
     * @return float|int
     */
    protected function sendMessageVariable(Message $message, Variable $variable)
    {
        $marginal = $variable->getValue();
        $messageValue = $message->getValue();
        $logZ = GaussianDistribution::logProductNormalization($marginal, $messageValue);
        $variable->setValue(GaussianDistribution::multiply($marginal, $messageValue));

        return $logZ;
    }

    public function createVariableToMessageBinding(Variable $variable): Message
    {
        $newDistribution = GaussianDistribution::fromPrecisionMean(0, 0);
        $binding = parent::createVariableToMessageBindingWithMessage($variable,
            new Message(
                $newDistribution,
                sprintf('message from %s to %s', $this, $variable)));

        return $binding;
    }
}
