<?php

declare(strict_types=1);

namespace Laragod\Skills\FactorGraphs;

/**
 * Helper class for computing the factor graph's normalization constant.
 *
 * @template TValue
 */
final class FactorList
{
    /** @var list<Factor<TValue>> */
    private array $list = [];

    public function getLogNormalization(): float
    {
        foreach ($this->list as $factor) {
            $factor->resetMarginals();
        }

        $sumLogZ = 0.0;

        foreach ($this->list as $factor) {
            $numberOfMessages = $factor->getNumberOfMessages();

            for ($j = 0; $j < $numberOfMessages; $j++) {
                $sumLogZ += $factor->sendMessageIndex($j);
            }
        }

        $sumLogS = 0.0;

        foreach ($this->list as $factor) {
            $sumLogS += $factor->getLogNormalization();
        }

        return $sumLogZ + $sumLogS;
    }

    public function count(): int
    {
        return count($this->list);
    }

    /**
     * @param  Factor<TValue>  $factor
     * @return Factor<TValue>
     */
    public function addFactor(Factor $factor): Factor
    {
        $this->list[] = $factor;

        return $factor;
    }
}
