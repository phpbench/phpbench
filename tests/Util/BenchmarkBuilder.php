<?php

namespace PhpBench\Tests\Util;

use DateTime;
use PhpBench\Model\Benchmark;
use PhpBench\Model\Suite;
use RuntimeException;

final class BenchmarkBuilder
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var SubjectBuilder[]
     */
    private $subjectBuilders = [];

    /**
     * @var SuiteBuilder|null
     */
    private $suiteBuilder = null;

    public function __construct(?SuiteBuilder $suiteBuilder, string $name)
    {
        $this->name = $name;
        $this->suiteBuilder = $suiteBuilder;
    }

    public static function create(string $name): self
    {
        return new self(null, $name);
    }

    public function subject(string $name): SubjectBuilder
    {
        $builder = SubjectBuilder::forBenchmarkBuilder($this, $name);
        $this->subjectBuilders[] = $builder;

        return $builder;
    }

    public function build(?Suite $suite = null): Benchmark
    {
        if (null === $suite) {
            $suite = new Suite(
                'testSuite',
                new DateTime()
            );
        }
        $benchmark = new Benchmark($suite, $this->name);

        foreach ($this->subjectBuilders as $builder) {
            $benchmark->addSubject($builder->build($benchmark));
        }

        return $benchmark;
    }

    public function end(): SuiteBuilder
    {
        if (null === $this->suiteBuilder) {
            throw new RuntimeException(
                'This benchmark builder was not created by a suite builder, end() cannot return anything'
            );
        }

        return $this->suiteBuilder;
    }

    public static function forSuiteBuilder(SuiteBuilder $builder, string $name): self
    {
        return new self($builder, $name);
    }
}
