<?php

declare(strict_types=1);

namespace Laragod\Skills\FactorGraphs;

/**
 * Helper class for computing the factor graph's normalization constant.
 */
class FactorList
{
    private $_list = [];

    public function getLogNormalization()
    {
        $list = $this->_list;
        foreach ($list as &$currentFactor) {
            $currentFactor->resetMarginals();
        }

        $sumLogZ = 0.0;

        $listCount = count($this->_list);

        for ($i = 0; $i < $listCount; $i++) {
            $f = $this->_list[$i];

            $numberOfMessages = $f->getNumberOfMessages();

            for ($j = 0; $j < $numberOfMessages; $j++) {
                $sumLogZ += $f->sendMessageIndex($j);
            }
        }

        $sumLogS = 0;

        foreach ($list as &$currentFactor) {
            $sumLogS += $currentFactor->getLogNormalization();
        }

        return $sumLogZ + $sumLogS;
    }

    public function count(): int
    {
        return count($this->_list);
    }

    public function addFactor(Factor $factor): Factor
    {
        $this->_list[] = $factor;

        return $factor;
    }
}
