<?php

namespace PhpBench\Tests\Util;

use DateTime;
use PhpBench\Model\Benchmark;
use PhpBench\Model\ResolvedExecutor;
use PhpBench\Model\Subject;
use PhpBench\Model\Suite;
use PhpBench\Registry\Config;
use RuntimeException;

final class SubjectBuilder
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var VariantBuilder[]
     */
    private $variantBuilders = [];

    /**
     * @var BenchmarkBuilder|null
     */
    private $benchmarkBuilder;

    /**
     * @var string[]
     */
    private $groups = [];

    public function __construct(?BenchmarkBuilder $benchmarkBuilder, string $name)
    {
        $this->name = $name;
        $this->benchmarkBuilder = $benchmarkBuilder;
    }

    public static function create(string $name): self
    {
        return new self(null, $name);
    }

    public static function forBenchmarkBuilder(BenchmarkBuilder $builder, string $name): self
    {
        return new self($builder, $name);
    }

    public function variant(?string $name = null): VariantBuilder
    {
        $builder = VariantBuilder::forSubjectBuilder($this, $name);
        $this->variantBuilders[] = $builder;

        return $builder;
    }

    public function build(?Benchmark $benchmark = null): Subject
    {
        if (null === $benchmark) {
            $suite = new Suite(
                'testSuite',
                new DateTime()
            );
            $benchmark = new Benchmark($suite, 'testBenchmark');
        }

        $subject = new Subject($benchmark, $this->name);
        $subject->setGroups($this->groups);
        $subject->setExecutor(new ResolvedExecutor('example', new Config('config', [])));

        foreach ($this->variantBuilders as $builder) {
            $subject->setVariant($builder->build($subject));
        }

        return $subject;
    }

    /**
     * @param string[] $groups
     */
    public function withGroups(array $groups): SubjectBuilder
    {
        $this->groups = $groups;

        return $this;
    }

    public function end(): BenchmarkBuilder
    {
        if (null === $this->benchmarkBuilder) {
            throw new RuntimeException(
                'This subject builder was not created by a benchmark builder, end() cannot return anything'
            );
        }

        return $this->benchmarkBuilder;
    }
}
