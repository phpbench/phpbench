<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace PhpBench\Model;

use PhpBench\Math\Statistics;

/**
 * Provides summary statistics for the entires suite.
 */
class Summary
{
    private $nbSubjects = 0;
    private $nbIterations = 0;
    private $nbRejects = 0;
    private $nbRevolutions = 0;
    private $nbFailures = 0;
    private $nbWarnings = 0;
    private $stats = [
        'stdev' => [],
        'mean' => [],
        'mode' => [],
        'rstdev' => [],
        'variance' => [],
        'min' => [],
        'max' => [],
        'sum' => [],
    ];
    private $errorStacks = [];

    /**
     * @param Suite $suite
     */
    public function __construct(Suite $suite)
    {
        foreach ($suite->getBenchmarks() as $benchmark) {
            foreach ($benchmark->getSubjects() as $subject) {
                $this->nbSubjects++;

                foreach ($subject->getVariants() as $variant) {
                    $this->nbIterations += count($variant);
                    $this->nbRevolutions += $variant->getRevolutions();
                    $this->nbFailures += count($variant->getFailures());
                    $this->nbWarnings += count($variant->getWarnings());
                    $this->nbRejects += $variant->getRejectCount();

                    if ($variant->hasErrorStack()) {
                        $this->errorStacks[] = $variant->getErrorStack();
                        continue;
                    }

                    foreach ($variant->getStats() as $name => $value) {
                        $this->stats[$name][] = $value;
                    }
                }
            }
        }
    }

    public function getNbSubjects()
    {
        return $this->nbSubjects;
    }

    public function getNbIterations()
    {
        return $this->nbIterations;
    }

    public function getNbRejects()
    {
        return $this->nbRejects;
    }

    public function getNbRevolutions()
    {
        return $this->nbRevolutions;
    }

    public function getNbFailures()
    {
        return $this->nbFailures;
    }

    public function getNbWarnings()
    {
        return $this->nbWarnings;
    }

    public function getStats()
    {
        return $this->stats;
    }

    public function getMinTime()
    {
        return $this->stats['min'] ? min($this->stats['min']) : 0;
    }

    public function getMaxTime()
    {
        return $this->stats['max'] ? min($this->stats['max']) : 0;
    }

    public function getMeanTime()
    {
        return Statistics::mean($this->stats['mean']);
    }

    public function getModeTime()
    {
        return Statistics::mean($this->stats['mode']);
    }

    public function getTotalTime()
    {
        return array_sum($this->stats['sum']);
    }

    public function getMeanStDev()
    {
        return Statistics::mean($this->stats['stdev']);
    }

    public function getMeanRelStDev()
    {
        return Statistics::mean($this->stats['rstdev']);
    }
}
