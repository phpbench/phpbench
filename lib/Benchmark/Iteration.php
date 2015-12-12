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

use PhpBench\Benchmark\Metadata\SubjectMetadata;

/**
 * Represents the data required to execute a single iteration.
 */
class Iteration
{
    private $collection;
    private $warmup;
    private $revolutions;
    private $parameters = array();
    private $index;

    private $result;
    private $deviation;
    private $rejectionCount = 0;
    private $zValue;

    /**
     * @param int $index
     * @param int $revolutions
     * @param array $parameters
     */
    public function __construct(
        $index,
        IterationCollection $collection,
        $revolutions,
        $warmup,
        ParameterSet $parameters
    ) {
        $this->index = $index;
        $this->revolutions = $revolutions;
        $this->parameters = $parameters;
        $this->collection = $collection;
        $this->warmup = $warmup;
    }

    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * Return the index of this iteration.
     *
     * @return int
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * Get the subject metadata for this iteration.
     *
     * @return SubjectMetadata
     */
    public function getSubject()
    {
        return $this->collection->getSubject();
    }

    /**
     * Get the number of revolutions for this iteration.
     *
     * @return int
     */
    public function getRevolutions()
    {
        return $this->revolutions;
    }

    /**
     * Get the parameter set for this iteration.
     *
     * @return ParameterSet
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Associate the result of the iteration with the iteration.
     *
     * @param int
     */
    public function setResult(IterationResult $result)
    {
        $this->result = $result;
    }

    /**
     * Return the result.
     *
     * Throw an exception if the result has not yet been set.
     *
     * @throws \RuntimeException
     *
     * @return IterationResult
     */
    public function getResult()
    {
        if (!$this->result) {
            throw new \RuntimeException(
                'The iteration result has not been set yet.'
            );
        }

        return $this->result;
    }

    /**
     * Return true if this iteration has a result.
     *
     * @return bool
     */
    public function hasResult()
    {
        return null !== $this->result;
    }

    /**
     * Return the deviation from the mean for this iteration.
     *
     * @return float
     */
    public function getDeviation()
    {
        return $this->deviation;
    }

    /**
     * Set the computed deviation of this iteration from other iterations in the same
     * set.
     *
     * NOTE: Should be called by the IterationCollection
     *
     * @param int
     */
    public function setDeviation($deviation)
    {
        $this->deviation = $deviation;
    }

    /**
     * Get the computed ZValue for this iteration.
     *
     * @return float
     */
    public function getZValue()
    {
        return $this->zValue;
    }

    /**
     * Set the computed Z-Value for this iteration.
     *
     * @param float $zValue
     */
    public function setZValue($zValue)
    {
        $this->zValue = $zValue;
    }

    /**
     * Increase the reject count.
     */
    public function incrementRejectionCount()
    {
        $this->rejectionCount++;
    }

    /**
     * Return the number of times that this iteration was rejected.
     *
     * @return int
     */
    public function getRejectionCount()
    {
        return $this->rejectionCount;
    }

    /**
     * Return the number of warmup revolutions.
     */
    public function getWarmup()
    {
        return $this->warmup;
    }
}
