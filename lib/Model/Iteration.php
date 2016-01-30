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
        $rejectionCount = 0,
        $deviation = null,
        $zValue = null
    ) {
        $this->index = $index;
        $this->variant = $variant;
        $this->time = $time;
        $this->memory = $memory;
        $this->rejectionCount = $rejectionCount;
        $this->deviation = $deviation;
        $this->zValue = $zValue;
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
        return $this->time / $this->getVariant()->getSubject()->getRevs();
    }
}
