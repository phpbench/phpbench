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

use PhpBench\Util\TimeUnit;

/**
 * Subject representation.
 *
 * It represents the result rather than the details of
 * how to create that result.
 */
class Subject
{
    /**
     * @var Benchmark
     */
    private $benchmark;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string[]
     */
    private $groups = [];

    /**
     * @var int
     */
    private $sleep = 0;

    /**
     * @var float|null
     */
    private $retryThreshold;

    /**
     * @var string
     */
    private $outputTimeUnit = TimeUnit::MICROSECONDS;

    /**
     * @var int|null
     */
    private $outputTimePrecision = null;

    /**
     * @var string
     */
    private $outputMode = TimeUnit::MODE_TIME;

    /**
     * @var Variant[]
     */
    private $variants = [];

    /**
     * @var int
     */
    private $index = 0;

    /**
     * @var ResolvedExecutor
     */
    private $executor;

    /**
     */
    public function __construct(Benchmark $benchmark, string $name)
    {
        $this->benchmark = $benchmark;
        $this->name = $name;

        $this->index = count($benchmark->getSubjects());
    }

    /**
     * Return the method name of this subject.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Create and add a new variant based on this subject.
     *
     */
    public function createVariant(ParameterSet $parameterSet, int $revolutions, int $warmup, array $computedStats = []): Variant
    {
        $variant = new Variant(
            $this,
            $parameterSet,
            $revolutions,
            $warmup,
            $computedStats
        );
        $this->variants[$parameterSet->getName()] = $variant;

        return $variant;
    }

    /**
     * @return Variant[]
     */
    public function getVariants(): array
    {
        return $this->variants;
    }

    /**
     * Return the (containing) benchmark for this subject.
     */
    public function getBenchmark(): Benchmark
    {
        return $this->benchmark;
    }

    public function getGroups(): array
    {
        return $this->groups;
    }

    public function inGroups(array $groups): bool
    {
        return 0 !== count(array_intersect($this->groups, $groups));
    }

    public function setGroups(array $groups): void
    {
        $this->groups = $groups;
    }

    public function getSleep(): int
    {
        return $this->sleep;
    }

    public function setSleep(int $sleep): void
    {
        $this->sleep = $sleep;
    }

    public function getOutputTimeUnit(): ?string
    {
        return $this->outputTimeUnit;
    }

    public function setOutputTimeUnit(?string $outputTimeUnit): void
    {
        $this->outputTimeUnit = $outputTimeUnit;
    }

    public function getOutputTimePrecision(): ?int
    {
        return $this->outputTimePrecision;
    }

    public function setOutputTimePrecision(?int $outputTimePrecision): void
    {
        $this->outputTimePrecision = $outputTimePrecision;
    }

    public function getOutputMode(): ?string
    {
        return $this->outputMode;
    }

    public function setOutputMode(?string $outputMode): void
    {
        $this->outputMode = $outputMode;
    }

    public function getRetryThreshold(): ?float
    {
        return $this->retryThreshold;
    }

    public function setRetryThreshold(?float $retryThreshold): void
    {
        $this->retryThreshold = $retryThreshold;
    }

    public function getIndex(): int
    {
        return $this->index;
    }

    public function getExecutor(): ResolvedExecutor
    {
        return $this->executor;
    }

    public function setExecutor(ResolvedExecutor $executor): void
    {
        $this->executor = $executor;
    }

    public function remove(Variant $target): void
    {
        $this->variants = array_filter($this->variants, function (Variant $variant) use ($target) {
            return $variant !== $target;
        });
    }

    public function getVariant(string $parameterSetName): ?Variant
    {
        return $this->variants[$parameterSetName] ?? null;
    }
}
