<?php

declare(strict_types=1);

namespace Laragod\Skills\TrueSkill\Factors;

use InvalidArgumentException;
use Laragod\Skills\FactorGraphs\Message;
use Laragod\Skills\FactorGraphs\Variable;
use Laragod\Skills\Guard;
use Laragod\Skills\Numerics\BasicMath;
use Laragod\Skills\Numerics\GaussianDistribution;

/**
 * Factor that sums together multiple weighted Gaussians.
 *
 * See the accompanying math paper for more details.
 */
final class GaussianWeightedSumFactor extends GaussianFactor
{
    /**
     * For every message index, the order in which the variables take part in the update.
     * For example, the first entry is [0, 1, 2] corresponding to v[0] = a1*v[1] + a2*v[2].
     *
     * @var array<int, array<int, int>>
     */
    private array $variableIndexOrdersForWeights = [];

    /** @var array<int, array<int, float>> */
    private array $weights = [];

    /** @var array<int, array<int, float>> */
    private array $weightsSquared = [];

    /**
     * @param  Variable<GaussianDistribution>  $sumVariable
     * @param  list<Variable<GaussianDistribution>>  $variablesToSum
     * @param  list<float>  $variableWeights
     */
    public function __construct(Variable $sumVariable, array $variablesToSum, array $variableWeights)
    {
        if (count($variablesToSum) !== count($variableWeights)) {
            throw new InvalidArgumentException(sprintf('Expected one weight per variable, got %d variables and %d weights', count($variablesToSum), count($variableWeights)));
        }

        parent::__construct(self::createName($sumVariable, $variablesToSum, $variableWeights));

        $variableWeightsLength = count($variableWeights);

        // The first weights are a straightforward copy
        // v_0 = a_1*v_1 + a_2*v_2 + ... + a_n * v_n
        $this->weights[0] = [];
        $this->weightsSquared[0] = [];

        for ($i = 0; $i < $variableWeightsLength; $i++) {
            $this->weights[0][$i] = $variableWeights[$i];
            $this->weightsSquared[0][$i] = BasicMath::square($variableWeights[$i]);
        }

        // 0..n
        $this->variableIndexOrdersForWeights[0] = [];

        for ($i = 0; $i < $variableWeightsLength + 1; $i++) {
            $this->variableIndexOrdersForWeights[0][] = $i;
        }

        // The rest move the variables around and divide out the constant.
        // For example:
        // v_1 = (-a_2 / a_1) * v_2 + (-a3/a1) * v_3 + ... + (1.0 / a_1) * v_0
        // By convention, we'll put the v_0 term at the end
        $weightsLength = $variableWeightsLength + 1;

        for ($weightsIndex = 1; $weightsIndex < $weightsLength; $weightsIndex++) {
            $currentWeights = array_fill(0, $variableWeightsLength, 0.0);
            $currentWeightsSquared = array_fill(0, $variableWeightsLength, 0.0);

            $variableIndices = array_fill(0, $variableWeightsLength + 1, 0);
            $variableIndices[0] = $weightsIndex;

            $divisor = $variableWeights[$weightsIndex - 1];

            // keep a single variable to keep track of where we are in the array.
            // This is helpful since we skip over one of the spots
            $currentDestinationWeightIndex = 0;

            for ($currentWeightSourceIndex = 0; $currentWeightSourceIndex < $variableWeightsLength; $currentWeightSourceIndex++) {
                if ($currentWeightSourceIndex === $weightsIndex - 1) {
                    continue;
                }

                // Guard against division by zero (a weight of exactly 0 contributes nothing)
                $currentWeight = $divisor === 0.0 ? 0.0 : -$variableWeights[$currentWeightSourceIndex] / $divisor;

                $currentWeights[$currentDestinationWeightIndex] = $currentWeight;
                $currentWeightsSquared[$currentDestinationWeightIndex] = $currentWeight * $currentWeight;

                $variableIndices[$currentDestinationWeightIndex + 1] = $currentWeightSourceIndex + 1;
                $currentDestinationWeightIndex++;
            }

            // And the final one
            $finalWeight = $divisor === 0.0 ? 0.0 : 1.0 / $divisor;

            $currentWeights[$currentDestinationWeightIndex] = $finalWeight;
            $currentWeightsSquared[$currentDestinationWeightIndex] = BasicMath::square($finalWeight);
            $variableIndices[$variableWeightsLength] = 0;

            $this->variableIndexOrdersForWeights[$weightsIndex] = $variableIndices;
            $this->weights[$weightsIndex] = $currentWeights;
            $this->weightsSquared[$weightsIndex] = $currentWeightsSquared;
        }

        $this->createVariableToMessageBinding($sumVariable);

        foreach ($variablesToSum as $currentVariable) {
            $this->createVariableToMessageBinding($currentVariable);
        }
    }

    public function getLogNormalization(): float
    {
        $variables = $this->getVariables();
        $messages = $this->getMessages();

        $result = 0.0;

        // We start at 1 since offset 0 has the sum
        $variableCount = count($variables);

        for ($i = 1; $i < $variableCount; $i++) {
            $result += GaussianDistribution::logRatioNormalization($variables[$i]->getValue(), $messages[$i]->getValue());
        }

        return $result;
    }

    public function updateMessageIndex(int $messageIndex): float
    {
        $allMessages = $this->getMessages();
        $allVariables = $this->getVariables();

        Guard::argumentIsValidIndex($messageIndex, count($allMessages), 'messageIndex');

        $updatedMessages = [];
        $updatedVariables = [];

        $indicesToUse = $this->variableIndexOrdersForWeights[$messageIndex];

        // The tricky part here is that we have to put the messages and variables in the same
        // order as the weights. Thankfully, the weights and messages share the same index numbers,
        // so we just need to make sure they're consistent
        $allMessagesCount = count($allMessages);

        for ($i = 0; $i < $allMessagesCount; $i++) {
            $updatedMessages[] = $allMessages[$indicesToUse[$i]];
            $updatedVariables[] = $allVariables[$indicesToUse[$i]];
        }

        return $this->updateHelper(
            $this->weights[$messageIndex],
            $this->weightsSquared[$messageIndex],
            $updatedMessages,
            $updatedVariables
        );
    }

    /**
     * @param  array<int, float>  $weights
     * @param  array<int, float>  $weightsSquared
     * @param  list<Message<GaussianDistribution>>  $messages
     * @param  list<Variable<GaussianDistribution>>  $variables
     */
    private function updateHelper(array $weights, array $weightsSquared, array $messages, array $variables): float
    {
        // Potentially look at http://mathworld.wolfram.com/NormalSumDistribution.html for clues as
        // to what it's doing
        $message0 = $messages[0]->getValue();
        $marginal0 = $variables[0]->getValue();

        // The math works out so that 1/newPrecision = sum of a_i^2 /marginalsWithoutMessages[i]
        $inverseOfNewPrecisionSum = 0.0;
        $weightedMeanSum = 0.0;

        $weightsSquaredLength = count($weightsSquared);

        for ($i = 0; $i < $weightsSquaredLength; $i++) {
            // These flow directly from the paper
            $marginal = $variables[$i + 1]->getValue();
            $message = $messages[$i + 1]->getValue();
            $precisionWithoutMessage = $marginal->getPrecision() - $message->getPrecision();

            $inverseOfNewPrecisionSum += $weightsSquared[$i] / $precisionWithoutMessage;

            $weightedMeanSum += $weights[$i]
                * ($marginal->getPrecisionMean() - $message->getPrecisionMean())
                / $precisionWithoutMessage;
        }

        $newPrecision = 1.0 / $inverseOfNewPrecisionSum;
        $newPrecisionMean = $newPrecision * $weightedMeanSum;

        $newMessage = GaussianDistribution::fromPrecisionMean($newPrecisionMean, $newPrecision);
        $oldMarginalWithoutMessage = GaussianDistribution::divide($marginal0, $message0);

        $newMarginal = GaussianDistribution::multiply($oldMarginalWithoutMessage, $newMessage);

        // Update the message and marginal
        $messages[0]->setValue($newMessage);
        $variables[0]->setValue($newMarginal);

        // Return the difference in the new marginal
        return GaussianDistribution::absoluteDifference($newMarginal, $marginal0);
    }

    /**
     * @param  Variable<GaussianDistribution>  $sumVariable
     * @param  list<Variable<GaussianDistribution>>  $variablesToSum
     * @param  list<float>  $weights
     */
    private static function createName(Variable $sumVariable, array $variablesToSum, array $weights): string
    {
        $result = $sumVariable.' = ';

        $totalVars = count($variablesToSum);

        for ($i = 0; $i < $totalVars; $i++) {
            if ($i === 0 && $weights[$i] < 0) {
                $result .= '-';
            }

            $result .= sprintf('%.2f*[%s]', abs($weights[$i]), $variablesToSum[$i]);

            if ($i !== $totalVars - 1) {
                $result .= $weights[$i + 1] >= 0 ? ' + ' : ' - ';
            }
        }

        return $result;
    }
}
