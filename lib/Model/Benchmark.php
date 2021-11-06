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

use ArrayIterator;
use PhpBench\Benchmark\Metadata\SubjectMetadata;
use RuntimeException;

/**
 * Benchmark metadata class.
 *
 * @implements \IteratorAggregate<Subject>
 */
class Benchmark implements \IteratorAggregate
{
    /**
     * @var string
     */
    private $class;

    /**
     * @var Subject[]
     */
    private $subjects = [];

    /**
     * @var Suite
     */
    private $suite;

    /**
     */
    public function __construct(Suite $suite, string $class)
    {
        $this->suite = $suite;
        $this->class = $class;
    }

    public function createSubjectFromMetadataAndExecutor(SubjectMetadata $metadata, ResolvedExecutor $executor): Subject
    {
        $subject = new Subject($this, $metadata->getName());
        $subject->setGroups($metadata->getGroups() ?: []);
        $subject->setSleep($metadata->getSleep() ?: 0);
        $subject->setRetryThreshold($metadata->getRetryThreshold());
        $subject->setOutputTimeUnit($metadata->getOutputTimeUnit());
        $subject->setOutputTimePrecision($metadata->getOutputTimePrecision());
        $subject->setOutputMode($metadata->getOutputMode());
        $subject->setExecutor($executor);
        $subject->setFormat($metadata->getFormat());

        $this->subjects[] = $subject;

        return $subject;
    }

    /**
     * Create and add a subject.
     *
     */
    public function createSubject(string $name): Subject
    {
        $subject = new Subject($this, $name);
        $this->subjects[$name] = $subject;

        return $subject;
    }

    public function addSubject(Subject $subject): void
    {
        if ($subject->getBenchmark() !== $this) {
            throw new RuntimeException(
                'Adding subject to benchmark to which it does not belong'
            );
        }

        $this->subjects[$subject->getName()] = $subject;
    }

    /**
     * Get the subject metadata instances for this benchmark metadata.
     *
     * @return array<Subject>
     */
    public function getSubjects(): array
    {
        return $this->subjects;
    }

    /**
     * Return the benchmark class.
     */
    public function getClass(): string
    {
        return $this->class;
    }

    public function getName(): string
    {
        $parts = explode('\\', $this->class);
        end($parts);

        return current($parts);
    }

    /**
     * Return the suite to which this benchmark belongs.
     */
    public function getSuite(): Suite
    {
        return $this->suite;
    }

    /**
     * @return ArrayIterator<Subject>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->subjects);
    }

    public function getSubject(string $subjectName): ?Subject
    {
        return $this->subjects[$subjectName] ?? null;
    }

    /**
     * @param string[] $subjectPatterns
     * @param string[] $variantPatterns
     */
    public function filter(array $subjectPatterns, array $variantPatterns): self
    {
        $subjects = array_filter($this->subjects, function (Subject $subject) use ($subjectPatterns) {
            return Subject::matchesPatterns($this->class, $subject->getName(), $subjectPatterns);
        });
        $subjects = array_map(function (Subject $subject) use ($variantPatterns) {
            return $subject->filterVariants($variantPatterns);
        }, $subjects);

        $new = clone $this;
        $new->subjects = $subjects;

        return $new;
    }
}
