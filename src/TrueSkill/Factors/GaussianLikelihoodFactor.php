<?php

declare(strict_types=1);

namespace Laragod\Skills\TrueSkill\Factors;

use Laragod\Skills\FactorGraphs\Message;
use Laragod\Skills\FactorGraphs\Variable;
use Laragod\Skills\Guard;
use Laragod\Skills\Numerics\GaussianDistribution;

/**
 * Connects two variables and adds uncertainty.
 *
 * See the accompanying math paper for more details.
 */
final class GaussianLikelihoodFactor extends GaussianFactor
{
    private readonly float $precision;

    /**
     * @param  Variable<GaussianDistribution>  $variable1
     * @param  Variable<GaussianDistribution>  $variable2
     */
    public function __construct(float $betaSquared, Variable $variable1, Variable $variable2)
    {
        parent::__construct(sprintf('Likelihood of %s going to %s', $variable2, $variable1));
        $this->precision = 1.0 / $betaSquared;
        $this->createVariableToMessageBinding($variable1);
        $this->createVariableToMessageBinding($variable2);
    }

    public function getLogNormalization(): float
    {
        return GaussianDistribution::logRatioNormalization(
            $this->getVariables()[0]->getValue(),
            $this->getMessages()[0]->getValue()
        );
    }

    public function updateMessageIndex(int $messageIndex): float
    {
        $messages = $this->getMessages();
        $variables = $this->getVariables();

        Guard::argumentIsValidIndex($messageIndex, count($messages), 'messageIndex');

        return $messageIndex === 0
            ? $this->updateHelper($messages[0], $messages[1], $variables[0], $variables[1])
            : $this->updateHelper($messages[1], $messages[0], $variables[1], $variables[0]);
    }

    /**
     * @param  Message<GaussianDistribution>  $message1
     * @param  Message<GaussianDistribution>  $message2
     * @param  Variable<GaussianDistribution>  $variable1
     * @param  Variable<GaussianDistribution>  $variable2
     */
    private function updateHelper(Message $message1, Message $message2, Variable $variable1, Variable $variable2): float
    {
        $message1Value = $message1->getValue();
        $message2Value = $message2->getValue();

        $marginal1 = $variable1->getValue();
        $marginal2 = $variable2->getValue();

        $a = $this->precision / ($this->precision + $marginal2->getPrecision() - $message2Value->getPrecision());

        $newMessage = GaussianDistribution::fromPrecisionMean(
            $a * ($marginal2->getPrecisionMean() - $message2Value->getPrecisionMean()),
            $a * ($marginal2->getPrecision() - $message2Value->getPrecision())
        );

        $oldMarginalWithoutMessage = GaussianDistribution::divide($marginal1, $message1Value);

        $newMarginal = GaussianDistribution::multiply($oldMarginalWithoutMessage, $newMessage);

        // Update the message and marginal
        $message1->setValue($newMessage);
        $variable1->setValue($newMarginal);

        // Return the difference in the new marginal
        return GaussianDistribution::absoluteDifference($newMarginal, $marginal1);
    }
}
