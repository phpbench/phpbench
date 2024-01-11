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
     * @param string[]|null $assert
     * @param int[]|null $iterations
     * @param int[]|null $revs
     * @param int[]|null $warmup
     */
    public function __construct(
        private readonly DriverInterface $innerDriver,
        private readonly ?array          $assert,
        private readonly ?string         $executor,
        private readonly ?string         $format,
        private readonly ?array          $iterations,
        private readonly ?string         $mode,
        private readonly ?string         $timeUnit,
        private readonly ?array          $revs,
        private readonly ?float          $timeout,
        private readonly ?array          $warmup,
        private readonly ?float          $retryThreshold
    ) {
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
        if ($this->assert && empty($subject->getAssertions())) {
            $subject->setAssertions($this->assert);
        }

        if ($this->executor && null === $subject->getExecutor()) {
            $subject->setExecutor(new ExecutorMetadata($this->executor, []));
        }

        if ($this->format && null === $subject->getFormat()) {
            $subject->setFormat($this->format);
        }

        if ($this->iterations && null === $subject->getIterations()) {
            $subject->setIterations($this->iterations);
        }

        if ($this->revs && null === $subject->getRevs()) {
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

        if ($this->retryThreshold && null === $subject->getRetryThreshold()) {
            $subject->setRetryThreshold($this->retryThreshold);
        }
    }
}
