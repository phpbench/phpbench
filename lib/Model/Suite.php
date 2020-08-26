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

use IteratorAggregate;
use PhpBench\Assertion\AssertionFailures;
use PhpBench\Assertion\AssertionWarnings;
use PhpBench\Environment\Information;

/**
 * Represents a Suite.
 *
 * This is the base of the object graph created by the Runner.
 *
 * @implements IteratorAggregate<Benchmark>
 */
class Suite implements IteratorAggregate
{
    private $tag;
    private $date;
    private $configPath;
    private $envInformations = [];
    private $benchmarks = [];
    private $uuid;

    /**
     * __construct.
     *
     * @param array $benchmarks
     * @param string $tag
     * @param \DateTime $date
     * @param string $configPath
     * @param Information[] $envInformations
     */
    public function __construct(
        $tag,
        \DateTime $date,
        $configPath = null,
        array $benchmarks = [],
        array $envInformations = [],
        $uuid = null
    ) {
        $this->tag = $tag ? new Tag($tag) : null;
        $this->date = $date;
        $this->configPath = $configPath;
        $this->envInformations = $envInformations;
        $this->benchmarks = $benchmarks;
        $this->uuid = $uuid;
    }

    /**
     * @return array<Benchmark>
     */
    public function getBenchmarks()
    {
        return $this->benchmarks;
    }

    public function getBenchmark(string $class): ?Benchmark
    {
        return $this->benchmarks[$class] ?? null;
    }

    /**
     * Create and add a benchmark.
     *
     * @param string $class
     *
     * @return Benchmark
     */
    public function createBenchmark($class)
    {
        $benchmark = new Benchmark($this, $class);
        $this->benchmarks[$class] = $benchmark;

        return $benchmark;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->benchmarks);
    }

    public function getTag()
    {
        return $this->tag;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function getConfigPath()
    {
        return $this->configPath;
    }

    public function getSummary(): Summary
    {
        return new Summary($this);
    }

    public function getIterations()
    {
        $iterations = [];

        foreach ($this->getVariants() as $variant) {
            foreach ($variant as $iteration) {
                $iterations[] = $iteration;
            }
        }

        return $iterations;
    }

    public function getSubjects()
    {
        $subjects = [];

        foreach ($this->getBenchmarks() as $benchmark) {
            foreach ($benchmark->getSubjects() as $subject) {
                $subjects[] = $subject;
            }
        }

        return $subjects;
    }

    /**
     * @return array<Variant>
     */
    public function getVariants()
    {
        $variants = [];

        foreach ($this->getSubjects() as $subject) {
            foreach ($subject->getVariants() as $variant) {
                $variants[] = $variant;
            }
        }

        return $variants;
    }

    public function getErrorStacks()
    {
        $errorStacks = [];

        foreach ($this->getVariants() as $variant) {
            if (false === $variant->hasErrorStack()) {
                continue;
            }

            $errorStacks[] = $variant->getErrorStack();
        }

        return $errorStacks;
    }

    /**
     * @return AssertionFailures[]
     */
    public function getFailures()
    {
        $failures = [];

        /** @var Variant $variant */
        foreach ($this->getVariants() as $variant) {
            if (false === $variant->hasFailed()) {
                continue;
            }

            $failures[] = $variant->getFailures();
        }

        return $failures;
    }

    /**
     * @return AssertionWarnings[]
     */
    public function getWarnings()
    {
        $warnings = [];

        /** @var Variant $variant */
        foreach ($this->getVariants() as $variant) {
            if (false === $variant->hasWarning()) {
                continue;
            }

            $warnings[] = $variant->getWarnings();
        }

        return $warnings;
    }

    /**
     * @param Information[] $envInformations
     */
    public function setEnvInformations(iterable $envInformations)
    {
        foreach ($envInformations as $envInformation) {
            $this->addEnvInformation($envInformation);
        }
    }

    public function addEnvInformation(Information $information)
    {
        $this->envInformations[$information->getName()] = $information;
    }

    /**
     * @return Information[]
     */
    public function getEnvInformations()
    {
        return $this->envInformations;
    }

    /**
     * The uuid uniquely identifies this suite.
     *
     * The uuid is determined by the storage driver, and may be empty
     * only when dynamically generating reports on-the-fly.
     *
     * @return mixed
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * Generate a universally unique identifier.
     *
     * The first 7 characters are the year month and in hex, the rest is a
     * truncated sha1 string encoding the environmental information, the
     * microtime and the configuration path.
     */
    public function generateUuid(): void
    {
        $serialized = serialize($this->envInformations);
        $this->uuid = dechex($this->getDate()->format('Ymd')) . substr(sha1(implode([
            microtime(),
            $serialized,
            $this->configPath,
        ])), 0, -7);
    }

    public function mergeBaselines(SuiteCollection $suiteCollection): self
    {
        foreach ($suiteCollection->getSuites() as $baselineSuite) {
            foreach ($this->getVariants() as $variant) {
                $subject = $variant->getSubject();
                $benchmark = $subject->getBenchmark();

                $baselineVariant = $baselineSuite->findVariant(
                    $benchmark->getClass(),
                    $subject->getName(),
                    $variant->getParameterSet()->getName()
                );

                if (!$baselineVariant) {
                    continue;
                }

                $variant->attachBaseline($baselineVariant);
            }
        }

        return $this;
    }

    public function findVariant(string $benchmarkClass, string $subjectName, string $variantName): ?Variant
    {
        if (!$benchmark = $this->getBenchmark($benchmarkClass)) {
            return null;
        }

        if (!$subject = $benchmark->getSubject($subjectName)) {
            return null;
        }

        return $subject->getVariant($variantName);
    }
}
