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
use DateTime;
use IteratorAggregate;
use PhpBench\Assertion\VariantAssertionResults;
use PhpBench\Environment\Information;
use RuntimeException;

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

    /**
     * @var Benchmark[]
     */
    private $benchmarks = [];
    private $uuid;

    /**
     * __construct.
     *
     * @param Information[] $envInformations
     */
    public function __construct(
        ?string $tag,
        DateTime $date,
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
     * @param string[] $subjectPatterns
     * @param string[] $variantPatterns
     */
    public function filter(array $subjectPatterns, array $variantPatterns): self
    {
        $new = clone $this;
        $benchmarks = array_map(function (Benchmark $benchmark) use ($subjectPatterns, $variantPatterns) {
            return $benchmark->filter($subjectPatterns, $variantPatterns);
        }, $this->benchmarks);
        $new->benchmarks = $benchmarks;

        return $new;
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

    public function addBenchmark(Benchmark $benchmark): void
    {
        if ($benchmark->getSuite() !== $this) {
            throw new RuntimeException(
                'Adding benchmark to suite to which it does not belong'
            );
        }

        $this->benchmarks[$benchmark->getClass()] = $benchmark;
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

    public function getDate(): DateTime
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

    /**
     * @return Subject[]
     */
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

    public function findVariantByParameterSetName(string $benchmarkClass, string $subjectName, string $variantName): ?Variant
    {
        if (!$benchmark = $this->getBenchmark($benchmarkClass)) {
            return null;
        }

        if (!$subject = $benchmark->getSubject($subjectName)) {
            return null;
        }

        return $subject->getVariant($variantName);
    }

    /**
     * @deprecated use findVariantByParameterSetName. will be removed in 2.0
     */
    public function findVariant(string $benchmarkClass, string $subjectName, string $variantName): ?Variant
    {
        return $this->findVariantByParameterSetName($benchmarkClass, $subjectName, $variantName);
    }

    public function getBaseline(): ?self
    {
        foreach ($this->getSubjects() as $subject) {
            foreach ($subject->getVariants() as $variant) {
                if ($variant->getBaseline()) {
                    return $variant->getBaseline()->getSubject()->getBenchmark()->getSuite();
                }
            }
        }

        return null;
    }
}
