<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Benchmark;

use PhpBench\Dom\Document;
use PhpBench\Math\Statistics;

/**
 * DOMDocument implementation for containing the benchmark suite
 * results.
 */
class SuiteDocument extends Document
{
    /**
     * Return a clone of the document>.
     *
     * @return SuiteDocument
     */
    public function duplicate()
    {
        $document = new self();
        $node = $document->importNode($this->firstChild, true);
        $document->appendChild($node);

        return $document;
    }

    /**
     * Return the number of subjects.
     *
     * @return int
     */
    public function getNbSubjects()
    {
        return (int) $this->evaluate('count(//subject)');
    }

    /**
     * Return the number of iterations.
     *
     * @return int
     */
    public function getNbIterations()
    {
        return (int) $this->evaluate('count(//iteration)');
    }

    /**
     * Return the number of revolutions.
     *
     * @return int
     */
    public function getNbRevolutions()
    {
        return (int) $this->evaluate('sum(//iteration/@revs)');
    }

    /**
     * Return the number of rejected iterations.
     *
     * @return int
     */
    public function getNbRejects()
    {
        return (int) $this->evaluate('sum(//iteration/@rejection-count)');
    }

    /**
     * Return total time taken by all iterations.
     *
     * @return int
     */
    public function getTotalTime()
    {
        return (float) $this->evaluate('sum(//iteration/@time-net)');
    }

    /**
     * Return the average standard deviation.
     *
     * @return float
     */
    public function getMeanStDev()
    {
        return (float) $this->evaluate('sum(//stats/@stdev) div count(//stats)');
    }

    /**
     * Return the average relative standard deviation of all the iteration sets.
     *
     * @return float
     */
    public function getMeanRelStDev()
    {
        $rStDevs = array();
        foreach ($this->query('//stats') as $stats) {
            $rStDevs[] = $stats->getAttribute('rstdev');
        }

        return Statistics::mean($rStDevs);
    }

    /**
     * Return the minimum time.
     *
     * @return float
     */
    public function getMin()
    {
        return min($this->getTimes());
    }

    /**
     * Return the mean time.
     *
     * @return float
     */
    public function getMeanTime()
    {
        return $this->getTotalTime() / $this->getNbRevolutions();
    }

    /**
     * Return the maximum time.
     *
     * @return float
     */
    public function getMax()
    {
        return max($this->getTimes());
    }

    /**
     * Return all times.
     *
     * @return float[]
     */
    public function getTimes()
    {
        $times = array();
        $nodes = $this->evaluate('.//iteration/@time');
        foreach ($nodes as $node) {
            $times[] = $node->nodeValue;
        }

        return $times;
    }

    /**
     * Return true if the suite contains errors.
     *
     * @return boolean
     */
    public function hasErrors()
    {
        return (boolean) $this->evaluate('count(//error)');
    }

}
