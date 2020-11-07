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
use IteratorAggregate;
use PhpBench\Assertion\VariantAssertionResults;
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
     * @param string $tag
     * @param string $configPath
     * @param Information[] $envInformations
     */
    public function __construct(
        ?string $tag,
        \DateTime $date,
        ?string $configPath = null,
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
    public function getBenchmarks(): array
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
     */
    public function createBenchmark(string $class): Benchmark
    {
        $benchmark = new Benchmark($this, $class);
        $this->benchmarks[$class] = $benchmark;

        return $benchmark;
    }

    /**
     * @return ArrayIterator<Benchmark>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->benchmarks);
    }

    public function getTag(): ?Tag
    {
        return $this->tag;
    }

    public function getDate(): \DateTime
    {
        return $this->date;
    }

    public function getConfigPath(): ?string
    {
        return $this->configPath;
    }

    public function getSummary(): Summary
    {
        return new Summary($this);
    }

    public function getIterations(): array
    {
        $iterations = [];

        foreach ($this->getVariants() as $variant) {
            foreach ($variant as $iteration) {
                $iterations[] = $iteration;
            }
        }

        return $iterations;
    }

    public function getSubjects(): array
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
    public function getVariants(): array
    {
        $variants = [];

        foreach ($this->getSubjects() as $subject) {
            foreach ($subject->getVariants() as $variant) {
                $variants[] = $variant;
            }
        }

        return $variants;
    }

    public function getErrorStacks(): array
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
     * @return VariantAssertionResults[]
     */
    public function getFailures(): array
    {
        $failures = [];

        /** @var Variant $variant */
        foreach ($this->getVariants() as $variant) {
            if (0 === $variant->getAssertionResults()->failures()->count()) {
                continue;
            }

            $failures[] = $variant->getAssertionResults()->failures();
        }

        return $failures;
    }

    /**
     * @return VariantAssertionResults[]
     */
    public function getWarnings(): array
    {
        $warnings = [];

        /** @var Variant $variant */
        foreach ($this->getVariants() as $variant) {
            if (0 === $variant->getAssertionResults()->warnings()->count()) {
                continue;
            }

            $warnings[] = $variant->getAssertionResults()->warnings();
        }

        return $warnings;
    }

    /**
     * @param Information[] $envInformations
     */
    public function setEnvInformations(iterable $envInformations): void
    {
        foreach ($envInformations as $envInformation) {
            $this->addEnvInformation($envInformation);
        }
    }

    public function addEnvInformation(Information $information): void
    {
        $this->envInformations[$information->getName()] = $information;
    }

    /**
     * @return Information[]
     */
    public function getEnvInformations(): array
    {
        return $this->envInformations;
    }

    /**
     * The uuid uniquely identifies this suite.
     *
     * The uuid is determined by the storage driver, and may be empty
     * only when dynamically generating reports on-the-fly.
     *
     */
    public function getUuid(): ?string
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
        $this->uuid = dechex((int)$this->getDate()->format('Ymd')) . substr(sha1(implode([
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
