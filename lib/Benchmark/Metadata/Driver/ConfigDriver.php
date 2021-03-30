<?php

namespace PhpBench\Benchmark\Metadata\Driver;

use PhpBench\Benchmark\Metadata\BenchmarkMetadata;
use PhpBench\Benchmark\Metadata\DriverInterface;
use PhpBench\Benchmark\Metadata\ExecutorMetadata;
use PhpBench\Benchmark\Metadata\SubjectMetadata;
use PhpBench\Reflection\ReflectionHierarchy;

class ConfigDriver implements DriverInterface
{
    /**
     * @var array|null
     */
    private $assert;
    /**
     * @var array|null
     */
    private $revs;
    /**
     * @var string|null
     */
    private $executor;
    /**
     * @var array
     */
    private $warmup;
    /**
     * @var float|null
     */
    private $timeout;

    /**
     * @var string|null
     */
    private $timeUnit;
    /**
     * @var string|null
     */
    private $mode;
    /**
     * @var array|null
     */
    private $iterations;
    /**
     * @var string|null
     */
    private $format;

    /**
     * @var DriverInterface
     */
    private $innerDriver;

    /**
     * @param string[] $assert
     * @param int[] $iterations
     * @param int[] $revs
     */
    public function __construct(
        DriverInterface $innerDriver,
        ?array $assert,
        ?string $executor,
        ?string $format,
        ?array $iterations,
        ?string $mode,
        ?string $timeUnit,
        ?array $revs,
        ?float $timeout,
        ?array $warmup
    ) {
        $this->assert = $assert;
        $this->executor = $executor;
        $this->format = $format;
        $this->iterations = $iterations;
        $this->mode = $mode;
        $this->timeUnit = $timeUnit;
        $this->revs = $revs;
        $this->timeout = $timeout;
        $this->warmup = $warmup;
        $this->innerDriver = $innerDriver;
    }


    /**
     * {@inheritDoc}
     */
    public function getMetadataForHierarchy(ReflectionHierarchy $classHierarchy): BenchmarkMetadata
    {
        $metadata = $this->innerDriver->getMetadataForHierarchy($classHierarchy);

        foreach ($metadata->getSubjects() as $subject) {
            $this->setDefaults($subject);
        }

        return $metadata;
    }

    private function setDefaults(SubjectMetadata $subject): void
    {
        if (empty($subject->getAssertions())) {
            $subject->setAssertions($this->assert);
        }

        if ($this->executor && null === $subject->getExecutor()) {
            $subject->setExecutor(new ExecutorMetadata($this->executor, []));
        }

        if ($this->format && null === $subject->getFormat()) {
            $subject->setFormat($this->format);
        }

        // TODO: Make this empty and set default in config
        if ($this->iterations && [1] === $subject->getIterations()) {
            $subject->setIterations($this->iterations);
        }

        // TODO: Make this empty and set default in config
        if ($this->revs && [1] === $subject->getRevs()) {
            $subject->setRevs($this->revs);
        }

        if ($this->mode && null === $subject->getOutputMode()) {
            $subject->setOutputMode($this->mode);
        }

        if ($this->timeUnit && null === $subject->getOutputTimeUnit()) {
            $subject->setOutputTimeUnit($this->timeUnit);
        }

        if ($this->timeout && null === $subject->getTimeout()) {
            $subject->setTimeout($this->timeout);
        }

        if ($this->warmup && [0] === $subject->getWarmup()) {
            $subject->setWarmup($this->warmup);
        }
    }
}
