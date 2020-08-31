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

namespace PhpBench\Benchmark\Metadata;

/**
 * Metadata for benchmarkMetadata subjects.
 */
class SubjectMetadata
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array[]
     */
    private $parameterSets = [];

    /**
     * @var string[]
     */
    private $groups = [];

    /**
     * @var string[]
     */
    private $beforeMethods = [];

    /**
     * @var string[]
     */
    private $afterMethods = [];

    /**
     * @var string[]
     */
    private $paramProviders = [];

    /**
     * @var float
     */
    private $retryThreshold;

    /**
     * @var int[]
     */
    private $iterations = [1];

    /**
     * @var int[]
     */
    private $revs = [1];

    /**
     * @var int[]
     */
    private $warmup = [0];

    /**
     * @var bool
     */
    private $skip = false;

    /**
     * @var int
     */
    private $sleep = 0;

    /**
     * @var string
     */
    private $outputTimeUnit = null;

    /**
     * @var string
     */
    private $outputTimePrecision = null;

    /**
     * @var string
     */
    private $outputMode = null;

    /**
     * @var BenchmarkMetadata
     */
    private $benchmarkMetadata;

    /**
     * @var array<string>
     */
    private $assertions = [];

    /**
     * @var ExecutorMetadata
     */
    private $executorMetadata;

    /**
     * @var float|null
     */
    private $timeout = 0;

    /**
     */
    public function __construct(BenchmarkMetadata $benchmarkMetadata, string $name)
    {
        $this->name = $name;
        $this->benchmarkMetadata = $benchmarkMetadata;
    }

    /**
     * Return the method name of this subject.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the parameter sets for this subject.
     *
     * @param array[] $parameterSets
     */
    public function setParameterSets(array $parameterSets)
    {
        $this->parameterSets = $parameterSets;
    }

    /**
     * Return the parameter sets for this subject.
     *
     * @return array[]
     */
    public function getParameterSets()
    {
        return $this->parameterSets;
    }

    /**
     * Return the benchmarkMetadata metadata for this subject.
     *
     * @return BenchmarkMetadata
     */
    public function getBenchmark()
    {
        return $this->benchmarkMetadata;
    }

    public function getGroups()
    {
        return $this->groups;
    }

    public function inGroups(array $groups)
    {
        return (bool) count(array_intersect($this->groups, $groups));
    }

    public function setGroups(array $groups)
    {
        $this->groups = $groups;
    }

    public function getBeforeMethods()
    {
        return $this->beforeMethods;
    }

    public function setBeforeMethods(array $beforeMethods)
    {
        $this->beforeMethods = $beforeMethods;
    }

    public function getAfterMethods(): array
    {
        return $this->afterMethods;
    }

    public function setAfterMethods(array $afterMethods)
    {
        $this->afterMethods = $afterMethods;
    }

    public function getParamProviders()
    {
        return $this->paramProviders;
    }

    public function setParamProviders(array $paramProviders)
    {
        $this->paramProviders = $paramProviders;

        return $this;
    }

    public function getIterations()
    {
        return $this->iterations;
    }

    public function setIterations(array $iterations)
    {
        $this->iterations = $iterations;
    }

    public function getRevs()
    {
        return $this->revs;
    }

    public function setRevs(array $revs)
    {
        $this->revs = $revs;
    }

    public function getSkip()
    {
        return $this->skip;
    }

    public function setSkip(bool $skip)
    {
        $this->skip = $skip;
    }

    public function getSleep()
    {
        return $this->sleep;
    }

    public function setSleep(int $sleep)
    {
        $this->sleep = $sleep;
    }

    public function getOutputTimeUnit()
    {
        return $this->outputTimeUnit;
    }

    public function setOutputTimeUnit(?string $outputTimeUnit)
    {
        $this->outputTimeUnit = $outputTimeUnit;
    }

    public function getOutputTimePrecision()
    {
        return $this->outputTimePrecision;
    }

    public function setOutputTimePrecision(?string $outputTimePrecision)
    {
        $this->outputTimePrecision = $outputTimePrecision;
    }

    public function getOutputMode()
    {
        return $this->outputMode;
    }

    public function setOutputMode(?string $outputMode)
    {
        $this->outputMode = $outputMode;
    }

    public function getWarmup()
    {
        return $this->warmup;
    }

    public function setWarmup(array $warmup)
    {
        $this->warmup = $warmup;
    }

    public function getRetryThreshold()
    {
        return $this->retryThreshold;
    }

    public function setRetryThreshold(?float $retryThreshold): void
    {
        $this->retryThreshold = $retryThreshold;
    }

    public function addAssertion(string $assertion): void
    {
        $this->assertions[] = $assertion;
    }

    /**
     * @param array<string> $assertions
     */
    public function setAssertions(array $assertions): void
    {
        $this->assertions = [];

        foreach ($assertions as $assertion) {
            $this->addAssertion($assertion);
        }
    }

    /**
     * @return array<string>
     */
    public function getAssertions()
    {
        return $this->assertions;
    }

    /**
     * @return ExecutorMetadata|null
     */
    public function getExecutor()
    {
        return $this->executorMetadata;
    }

    public function setExecutor(ExecutorMetadata $serviceMetadata)
    {
        $this->executorMetadata = $serviceMetadata;
    }

    public function getTimeout(): ?float
    {
        return $this->timeout;
    }

    public function setTimeout(?float $timeout): void
    {
        $this->timeout = $timeout;
    }
}
