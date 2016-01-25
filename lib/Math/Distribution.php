<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Math;

/**
 * Represents a population of samples.
 *
 * Lazily Provides summary statistics, also traversable.
 */
class Distribution implements \IteratorAggregate
{
    private $samples = array();
    private $stats = array();
    private $closures = array();

    public function __construct(array $samples)
    {
        if (count($samples) < 1) {
            throw new \LogicException(
                'Cannot create a distribution with zero samples.'
            );
        }
        $this->samples = $samples;
        $this->closures = array(
            'min' => function () { return min($this->samples); },
            'max' => function () { return max($this->samples); },
            'sum' => function () { return array_sum($this->samples); },
            'stdev' => function () { return Statistics::stdev($this->samples); },
            'mean' => function () { return Statistics::mean($this->samples); },
            'mode' => function () { return Statistics::kdeMode($this->samples); },
            'variance' => function () { return Statistics::variance($this->samples); },
            'rstdev' => function () {
                $mean = $this->getMean();

                return $mean ? $this->getStdev() / $mean * 100 : 0;
            },
        );
    }

    public function getMin()
    {
        return $this->getStat('min');
    }

    public function getMax()
    {
        return $this->getStat('max');
    }

    public function getSum()
    {
        return $this->getStat('sum');
    }

    public function getStdev()
    {
        return $this->getStat('stdev');
    }

    public function getMean()
    {
        return $this->getStat('mean');
    }

    public function getMode()
    {
        return $this->getStat('mode');
    }

    public function getRstdev()
    {
        return $this->getStat('rstdev');
    }

    public function getVariance()
    {
        return $this->getStat('variance');
    }

    public function getIterator()
    {
        foreach ($this->closures as $name => $callback) {
            $this->stats[$name] = $callback();
        }

        return new \ArrayIterator($this->stats);
    }

    private function getStat($name)
    {
        if (isset($this->stats[$name])) {
            return $this->stats[$name];
        }

        $this->stats[$name] = $this->closures[$name]($this->samples, $this);

        return $this->stats[$name];
    }
}
