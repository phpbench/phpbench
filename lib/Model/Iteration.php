<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Model;

/**
 * Represents the data required to execute a single iteration.
 */
class Iteration
{
    private $variant;
    private $index;

    private $time;
    private $memory;
    private $deviation;
    private $rejectionCount = 0;
    private $zValue;

    /**
     * @param int $index
     * @param int $revolutions
     */
    public function __construct(
        $index,
        Variant $variant,
        $time = null,
        $memory = null,
        $deviation = null,
        $zValue = null,
        $rejectionCount = 0
    ) {
        $this->index = $index;
        $this->variant = $variant;
        $this->time = $time;
        $this->memory = $memory;
        $this->deviation = $deviation;
        $this->zValue = $zValue;
        $this->rejectionCount = $rejectionCount;
    }

    /**
     * Return the Variant that this
     * iteration belongs to.
     *
     * @return Variant
     */
    public function getVariant()
    {
        return $this->variant;
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
     * Associate the result of the iteration with the iteration.
     *
     * @param int
     */
    public function setResult(IterationResult $result)
    {
        $this->time = $result->getTime();
        $this->memory = $result->getMemory();
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
     * Return the subject this iteration is related to.
     * Proxy-method.
     *
     * @return Subject
     */
    public function getSubject()
    {
        return $this->getVariant()->getSubject();
    }

    /**
     * Return the parameter set for this iteration.
     *
     * @return ParameterSet
     */
    public function getParameters()
    {
        return $this->variant->getParameterSet();
    }

    /**
     * Return the number of warmup revolutions.
     *
     * @return int
     */
    public function getWarmup()
    {
        return $this->variant->getWarmup();
    }

    /**
     * Return the number of revolutions.
     *
     * @return int
     */
    public function getRevolutions()
    {
        return $this->variant->getRevolutions();
    }

    /**
     * Return the time taken (in microseconds) to perform this iteration (or
     * NULL if not yet performed.
     *
     * @return int
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * Return the memory (in bytes) taken to perform this iteration (or
     * NULL if not yet performed.
     *
     * @return int
     */
    public function getMemory()
    {
        return $this->memory;
    }

    /**
     * Return the revolution time.
     *
     * @return float
     */
    public function getRevTime()
    {
        return $this->time / $this->getRevolutions();
    }
}
