<?php

namespace PhpBench\Tests\Util;

use DateTime;
use PhpBench\Model\Suite;

final class SuiteBuilder
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var BenchmarkBuilder[]
     */
    private $benchmarkBuilders = [];

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public static function create(string $name): self
    {
        return new self($name);
    }

    public function benchmark(string $name): BenchmarkBuilder
    {
        $builder = BenchmarkBuilder::forSuiteBuilder($this, $name);
        $this->benchmarkBuilders[] = $builder;

        return $builder;
    }

    public function build(): Suite
    {
        $suite = new Suite(
            $this->name,
            new DateTime()
        );

        foreach ($this->benchmarkBuilders as $builder) {
            $suite->addBenchmark($builder->build($suite));
        }

        return $suite;
    }
}
