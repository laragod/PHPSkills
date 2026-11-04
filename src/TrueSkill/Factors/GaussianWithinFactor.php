<?php

declare(strict_types=1);

namespace Laragod\Skills\TrueSkill\Factors;

use Laragod\Skills\FactorGraphs\Message;
use Laragod\Skills\FactorGraphs\Variable;
use Laragod\Skills\Numerics\GaussianDistribution;
use Laragod\Skills\TrueSkill\TruncatedGaussianCorrectionFunctions;

/**
 * Factor representing a team difference that has not exceeded the draw margin.
 *
 * See the accompanying math paper for more details.
 */
final class GaussianWithinFactor extends GaussianFactor
{
    /**
     * @param  Variable<GaussianDistribution>  $variable
     */
    public function __construct(
        private readonly float $epsilon,
        Variable $variable,
    ) {
        parent::__construct(sprintf('%s <= %.2f', $variable, $epsilon));
        $this->createVariableToMessageBinding($variable);
    }

    public function getLogNormalization(): float
    {
        $marginal = $this->getVariables()[0]->getValue();
        $message = $this->getMessages()[0]->getValue();
        $messageFromVariable = GaussianDistribution::divide($marginal, $message);
        $mean = $messageFromVariable->getMean();
        $std = $messageFromVariable->getStandardDeviation();
        $z = GaussianDistribution::cumulativeTo(($this->epsilon - $mean) / $std)
            - GaussianDistribution::cumulativeTo((-$this->epsilon - $mean) / $std);

        return -GaussianDistribution::logProductNormalization($messageFromVariable, $message) + log($z);
    }

    /**
     * @param  Message<GaussianDistribution>  $message
     * @param  Variable<GaussianDistribution>  $variable
     */
    protected function updateMessageVariable(Message $message, Variable $variable): float
    {
        $oldMarginal = $variable->getValue();
        $oldMessage = $message->getValue();
        $messageFromVariable = GaussianDistribution::divide($oldMarginal, $oldMessage);

        $c = $messageFromVariable->getPrecision();
        $d = $messageFromVariable->getPrecisionMean();

        $sqrtC = sqrt($c);
        $dOnSqrtC = $d / $sqrtC;
        $epsilonTimesSqrtC = $this->epsilon * $sqrtC;

        $denominator = 1.0 - TruncatedGaussianCorrectionFunctions::wWithinMargin($dOnSqrtC, $epsilonTimesSqrtC);
        $newPrecision = $c / $denominator;
        $newPrecisionMean = ($d + $sqrtC * TruncatedGaussianCorrectionFunctions::vWithinMargin($dOnSqrtC, $epsilonTimesSqrtC)) / $denominator;

        $newMarginal = GaussianDistribution::fromPrecisionMean($newPrecisionMean, $newPrecision);
        $newMessage = GaussianDistribution::divide(
            GaussianDistribution::multiply($oldMessage, $newMarginal),
            $oldMarginal
        );

        // Update the message and marginal
        $message->setValue($newMessage);
        $variable->setValue($newMarginal);

        // Return the difference in the new marginal
        return GaussianDistribution::absoluteDifference($newMarginal, $oldMarginal);
    }
}
