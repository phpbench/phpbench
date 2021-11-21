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
use RuntimeException;

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
     * @var string
     */
    private $format;

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
     * @param array<string,mixed> $computedStats
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
        $this->variants[] = $variant;

        return $variant;
    }

    public function addVariant(Variant $variant): void
    {
        if ($variant->getSubject() !== $this) {
            throw new RuntimeException(
                'Adding variant to subject to which it does not belong'
            );
        }
        $this->variants[] = $variant;
    }

    /**
     * @deprecated Use addVariant. To be removed in 2.0
     */
    public function setVariant(Variant $variant): void
    {
        $this->addVariant($variant);
    }

    /**
     * @return Variant[]
     */
    public function getVariants(): array
    {
        return array_values($this->variants);
    }

    /**
     * Return the (containing) benchmark for this subject.
     */
    public function getBenchmark(): Benchmark
    {
        return $this->benchmark;
    }

    /**
     * @return string[]
     */
    public function getGroups(): array
    {
        return $this->groups;
    }

    /**
     * @param string[] $groups
     */
    public function inGroups(array $groups): bool
    {
        return 0 !== count(array_intersect($this->groups, $groups));
    }

    /**
     * @param string[] $groups
     */
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

    /**
     * Returns the _first_ variant that matches the given parameter set name.
     * Note that there may be multiple variants with the same parameter set as
     * they can also vary by the number of revs/iterations.
     */
    public function getVariantByParameterSetName(string $parameterSetName): ?Variant
    {
        foreach ($this->variants as $variant) {
            if ($variant->getParameterSet()->getName() !== $parameterSetName) {
                continue;
            }

            return $variant;
        }

        return null;
    }

    /**
     * @deprecated use getVariantByParameterSetName. will be removed in 2.0
     */
    public function getVariant(string $parameterSetName): ?Variant
    {
        return $this->getVariantByParameterSetName($parameterSetName);
    }

    public function setFormat(?string $format): void
    {
        $this->format = $format;
    }

    public function getFormat(): ?string
    {
        return $this->format;
    }

    /**
     * @param string[] $variantPatterns
     */
    public function filterVariants(array $variantPatterns): self
    {
        $new = clone $this;
        $new->variants = array_filter($this->variants, function (Variant $variant) use ($variantPatterns) {
            return $variant->getParameterSet()->nameMatches($variantPatterns);
        });

        return $new;
    }

    /**
     * @param string[] $patterns
     */
    public static function matchesPatterns(string $benchmark, string $subject, array $patterns): bool
    {
        if (empty($patterns)) {
            return true;
        }

        foreach ($patterns as $pattern) {
            if (preg_match(
                sprintf('{^.*?%s.*?$}', $pattern),
                sprintf('%s::%s', $benchmark, $subject)
            )) {
                return true;
            }
        }

        return false;
    }
}
