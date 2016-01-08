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
        return (int) $this->evaluate('number(//variant/@revs) * count(//iteration)');
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
        return (float) $this->evaluate('sum(//iteration/@net-time)');
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
    public function getMinTime()
    {
        return $this->getTimes() ? min($this->getTimes()) : 0;
    }

    /**
     * Return the mean time.
     *
     * @return float
     */
    public function getMeanTime()
    {
        return $this->getTotalTime() ? $this->getTotalTime() / $this->getNbRevolutions() : 0;
    }

    /**
     * Return the mode time.
     *
     * @return float
     */
    public function getModeTime()
    {
        return Statistics::kdeMode($this->getTimes());
    }

    /**
     * Return the maximum time.
     *
     * @return float
     */
    public function getMaxTime()
    {
        return $this->getTimes() ? max($this->getTimes()) : 0;
    }

    /**
     * Return all times.
     *
     * @return float[]
     */
    public function getTimes()
    {
        $times = array();
        $nodes = $this->evaluate('.//iteration/@rev-time');
        foreach ($nodes as $node) {
            $times[] = $node->nodeValue;
        }

        return $times;
    }

    /**
     * Return true if the suite contains errors.
     *
     * @return bool
     */
    public function hasErrors()
    {
        return (boolean) $this->evaluate('count(//error)');
    }

    /**
     * Return any errors reported in the document as an array.
     *
     * @return array
     */
    public function getErrorStacks()
    {
        $errors = array();
        foreach ($this->query('//errors') as $errorsEl) {
            $stack = array(
                'subject' => $errorsEl->evaluate('concat(ancestor::benchmark/@class, "::", ancestor::subject/@name)'),
                'exceptions' => array(),
            );
            foreach ($errorsEl->query('//error') as $errorEl) {
                $stack['exceptions'][] = array(
                    'exception_class' => $errorEl->getAttribute('exception-class'),
                    'message' => $errorEl->nodeValue,
                );
            }
            $errors[] = $stack;
        }

        return $errors;
    }

    /**
     * Append the suites container in another document to this document.
     *
     * @param SuiteDocument $suiteDocument
     * @param string $defaultName
     */
    public function appendSuiteDocument(SuiteDocument $suiteDocument, $defaultName = null)
    {
        $aggregateEl = $this->evaluate('/phpbench')->item(0);

        if (null === $aggregateEl) {
            throw new \InvalidArgumentException(
                'Suite document must have root element "phpbench" before appending other suites'
            );
        }

        foreach ($suiteDocument->xpath()->query('//suite') as $suiteEl) {
            $suiteEl = $this->importNode($suiteEl, true);

            if (!$suiteEl->getAttribute('name')) {
                $suiteEl->setAttribute('name', $defaultName);
            }

            $aggregateEl->appendChild($suiteEl);
        }
    }
}
