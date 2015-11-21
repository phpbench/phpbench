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
    private $subject;
    private $revolutions;
    private $parameters = array();
    private $index;

    private $result;
    private $deviation;
    private $rejectionCount = 0;

    /**
     * @param int $index
     * @param SubjectMetadata $subject
     * @param int $revolutions
     * @param array $parameters
     */
    public function __construct(
        $index,
        SubjectMetadata $subject,
        $revolutions,
        ParameterSet $parameters
    ) {
        $this->index = $index;
        $this->subject = $subject;
        $this->revolutions = $revolutions;
        $this->parameters = $parameters;
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
        return $this->subject;
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
     * Assosciate the result of the iteration with the iteration.
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
     * Return the deviation from the mean for this iteration.
     *
     * @return float
     */
    public function getDeviation()
    {
        return $this->deviation;
    }

    /**
     * Set the deviation of this iteration from other iterations in the same
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
}
