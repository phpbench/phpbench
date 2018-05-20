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

use PhpBench\Benchmark\Metadata\SubjectMetadata;
use PhpBench\Registry\Config;
use PhpBench\Model\ResolvedExecutor;

/**
 * Benchmark metadata class.
 */
class Benchmark implements \IteratorAggregate
{
    /**
     * @var string
     */
    private $class;

    /**
     * @var SubjectMetadata[]
     */
    private $subjects = [];

    /**
     * @var Suite
     */
    private $suite;

    /**
     * @param mixed $path
     * @param mixed $class
     * @param Subject[] $subjects
     */
    public function __construct(Suite $suite, $class)
    {
        $this->suite = $suite;
        $this->class = $class;
    }

    public function createSubjectFromMetadataAndExecutor(SubjectMetadata $metadata, ResolvedExecutor $executor)
    {
        $subject = new Subject($this, $metadata->getName());
        $subject->setGroups($metadata->getGroups());
        $subject->setSleep($metadata->getSleep());
        $subject->setRetryThreshold($metadata->getRetryThreshold());
        $subject->setOutputTimeUnit($metadata->getOutputTimeUnit());
        $subject->setOutputTimePrecision($metadata->getOutputTimePrecision());
        $subject->setOutputMode($metadata->getOutputMode());
        $subject->setExecutor($executor);

        $this->subjects[] = $subject;

        return $subject;
    }

    /**
     * Create and add a subject.
     *
     * @param string $name
     *
     * @return Subject
     */
    public function createSubject($name)
    {
        $subject = new Subject($this, $name);
        $this->subjects[$name] = $subject;

        return $subject;
    }

    /**
     * Get the subject metadata instances for this benchmark metadata.
     *
     * @return SubjectMetadata[]
     */
    public function getSubjects()
    {
        return $this->subjects;
    }

    /**
     * Return the benchmark class.
     *
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Return the suite to which this benchmark belongs.
     *
     * @return Suite
     */
    public function getSuite()
    {
        return $this->suite;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return $this->subjects;
    }
}
