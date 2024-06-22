<?php

namespace PhpBench\Tests\Util;

use DateTime;
use PhpBench\Model\Suite;

final class SuiteBuilder
{
    /**
     * @var BenchmarkBuilder[]
     */
    private array $benchmarkBuilders = [];

    private ?DateTime $date = null;

    public function __construct(private readonly string $name)
    {
    }

    public static function create(string $name): self
    {
        return new self($name);
    }

    public function withDateString(string $date): SuiteBuilder
    {
        $this->date = new DateTime($date);

        return $this;
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
            $this->date ?? new DateTime()
        );

        foreach ($this->benchmarkBuilders as $builder) {
            $suite->addBenchmark($builder->build($suite));
        }

        return $suite;
    }
}
