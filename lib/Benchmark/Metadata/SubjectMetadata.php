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
    private ParameterSetsCollection $parameterSets;

    /**
     * @var string[]|null
     */
    private ?array $groups = null;

    /**
     * @var string[]|null
     */
    private ?array $beforeMethods = null;

    /**
     * @var string[]|null
     */
    private ?array $afterMethods = null;

    /**
     * @var string[]|null
     */
    private ?array $paramProviders = null;

    private ?float $retryThreshold = null;

    /**
     * @var null|int[]
     */
    private ?array $iterations = null;

    /**
     * @var null|int[]
     */
    private ?array $revs = null;

    /**
     * @var null|int[]
     */
    private ?array $warmup = null;

    private ?bool $skip = null;

    private ?int $sleep = null;

    private ?string $outputTimeUnit = null;

    private ?int $outputTimePrecision = null;

    private ?string $outputMode = null;

    /**
     * @var null|array<string>
     */
    private ?array $assertions = null;

    private ?ExecutorMetadata $executorMetadata = null;

    private ?float $timeout = null;

    private ?int $retryLimit = null;

    private ?string $format = null;

    public function __construct(private readonly BenchmarkMetadata $benchmarkMetadata, private readonly string $name)
    {
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

    /**
     * @param string[] $groups
     */
    public function inGroups(array $groups): bool
    {
        if ($this->groups === null) {
            return false;
        }

        return (bool) count(array_intersect($this->groups, $groups));
    }

    /**
     * @param string[] $groups
     */
    public function setGroups(array $groups): void
    {
        $this->groups = $groups;
    }

    /**
     * @return string[]
     */
    public function getBeforeMethods(): array
    {
        return $this->beforeMethods ?: [];
    }

    /**
     * @param string[] $beforeMethods
     */
    public function setBeforeMethods(array $beforeMethods): void
    {
        $this->beforeMethods = $beforeMethods;
    }

    /**
     * @return string[]
     */
    public function getAfterMethods(): array
    {
        return $this->afterMethods ?: [];
    }

    /**
     * @param string[] $afterMethods
     */
    public function setAfterMethods(array $afterMethods): void
    {
        $this->afterMethods = $afterMethods;
    }

    /**
     * @return string[]
     */
    public function getParamProviders(): array
    {
        return $this->paramProviders ?: [];
    }

    /**
     * @param string[] $paramProviders
     */
    public function setParamProviders(array $paramProviders): self
    {
        $this->paramProviders = $paramProviders;

        return $this;
    }

    /**
     * @return int[]|null
     */
    public function getIterations(): ?array
    {
        return $this->iterations;
    }

    /**
     * @param int[] $iterations
     */
    public function setIterations(array $iterations): void
    {
        $this->iterations = $iterations;
    }

    /**
     * @return int[]|null
     */
    public function getRevs(): ?array
    {
        return $this->revs;
    }

    /**
     * @param int[] $revs
     */
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
