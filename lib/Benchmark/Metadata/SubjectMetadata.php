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

use PhpBench\Model\ParameterSetsCollection;

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
     * @var ParameterSetsCollection
     */
    private $parameterSets;

    /**
     * @var string[]|null
     */
    private $groups;

    /**
     * @var string[]|null
     */
    private $beforeMethods;

    /**
     * @var string[]|null
     */
    private $afterMethods;

    /**
     * @var string[]|null
     */
    private $paramProviders;

    /**
     * @var float|null
     */
    private $retryThreshold;

    /**
     * @var null|int[]
     */
    private $iterations = null;

    /**
     * @var null|int[]
     */
    private $revs = null;

    /**
     * @var null|int[]
     */
    private $warmup;

    /**
     * @var bool|null
     */
    private $skip;

    /**
     * @var int|null
     */
    private $sleep;

    /**
     * @var string|null
     */
    private $outputTimeUnit = null;

    /**
     * @var int|null
     */
    private $outputTimePrecision = null;

    /**
     * @var string|null
     */
    private $outputMode = null;

    /**
     * @var BenchmarkMetadata
     */
    private $benchmarkMetadata;

    /**
     * @var null|array<string>
     */
    private $assertions;

    /**
     * @var ExecutorMetadata|null
     */
    private $executorMetadata;

    /**
     * @var float|null
     */
    private $timeout;

    /**
     * @var int|null
     */
    private $retryLimit = null;

    /**
     * @var string|null
     */
    private $format = null;

    /**
     */
    public function __construct(BenchmarkMetadata $benchmarkMetadata, string $name)
    {
        $this->name = $name;
        $this->benchmarkMetadata = $benchmarkMetadata;
        $this->parameterSets = ParameterSetsCollection::empty();
    }

    /**
     * Return the method name of this subject.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set the parameter sets for this subject.
     */
    public function setParameterSets(ParameterSetsCollection $parameterSets): void
    {
        $this->parameterSets = $parameterSets;
    }

    /**
     * Return the parameter sets for this subject.
     */
    public function getParameterSetsCollection(): ParameterSetsCollection
    {
        return $this->parameterSets;
    }

    /**
     * Return the benchmarkMetadata metadata for this subject.
     */
    public function getBenchmark(): BenchmarkMetadata
    {
        return $this->benchmarkMetadata;
    }

    /**
     * @return string[]
     */
    public function getGroups(): array
    {
        return $this->groups ?: [];
    }

    public function inGroups(array $groups): bool
    {
        return (bool) count(array_intersect($this->groups, $groups));
    }

    public function setGroups(array $groups): void
    {
        $this->groups = $groups;
    }

    public function getBeforeMethods(): array
    {
        return $this->beforeMethods ?: [];
    }

    public function setBeforeMethods(array $beforeMethods): void
    {
        $this->beforeMethods = $beforeMethods;
    }

    public function getAfterMethods(): array
    {
        return $this->afterMethods ?: [];
    }

    public function setAfterMethods(array $afterMethods): void
    {
        $this->afterMethods = $afterMethods;
    }

    public function getParamProviders(): array
    {
        return $this->paramProviders ?: [];
    }

    public function setParamProviders(array $paramProviders): self
    {
        $this->paramProviders = $paramProviders;

        return $this;
    }

    public function getIterations(): ?array
    {
        return $this->iterations;
    }

    public function setIterations(array $iterations): void
    {
        $this->iterations = $iterations;
    }

    public function getRevs(): ?array
    {
        return $this->revs;
    }

    public function setRevs(array $revs): void
    {
        $this->revs = $revs;
    }

    public function getSkip(): bool
    {
        return $this->skip ?: false;
    }

    public function setSkip(bool $skip): void
    {
        $this->skip = $skip;
    }

    public function getSleep(): int
    {
        return $this->sleep ?: 0;
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

    /**
     * @return int[]
     */
    public function getWarmup(): array
    {
        return $this->warmup ?: [0];
    }

    /**
     * @param int[] $warmup
     */
    public function setWarmup(array $warmup): void
    {
        $this->warmup = $warmup;
    }

    public function getRetryThreshold(): ?float
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
     * @return string[]
     */
    public function getAssertions(): array
    {
        return $this->assertions ?: [];
    }

    public function setFormat(string $format): void
    {
        $this->format = $format;
    }

    public function getFormat(): ?string
    {
        return $this->format;
    }

    public function getExecutor(): ?ExecutorMetadata
    {
        return $this->executorMetadata;
    }

    public function setExecutor(ExecutorMetadata $serviceMetadata): void
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

    public function setRetryLimit(int $retryLimit): void
    {
        $this->retryLimit = $retryLimit;
    }

    public function getRetryLimit(): ?int
    {
        return $this->retryLimit;
    }

    public function merge(SubjectMetadata $subject): void
    {
        // merge
        foreach ([
            'groups',
            'beforeMethods',
            'afterMethods',
            'paramProviders',
            'iterations',
            'revs',
            'assertions',
            'warmup',
        ] as $toMerge) {
            if ($subject->$toMerge === null) {
                continue;
            }

            if ($this->$toMerge === null) {
                $this->$toMerge = $subject->$toMerge;

                continue;
            }
            $this->$toMerge = array_merge($this->$toMerge, $subject->$toMerge);
        }

        // replace
        foreach ([
            'skip',
            'sleep',
            'outputTimeUnit',
            'outputTimePrecision',
            'outputMode',
            'retryThreshold',
            'executorMetadata',
            'timeout',
        ] as $toReplace) {
            if ($subject->$toReplace === null) {
                continue;
            }
            $this->$toReplace = $subject->$toReplace;
        }
    }
}
