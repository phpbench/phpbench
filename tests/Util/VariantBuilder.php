<?php

namespace PhpBench\Tests\Util;

use DateTime;
use PhpBench\Model\Benchmark;
use PhpBench\Model\ParameterSet;
use PhpBench\Model\ResultInterface;
use PhpBench\Model\Subject;
use PhpBench\Model\Suite;
use PhpBench\Model\Variant;
use PhpBench\Tests\Unit\Model\IterationTest;
use PhpBench\Tests\Util\SubjectBuilder;

final class VariantBuilder
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var IterationBuilder[]
     */
    private $iterations;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public static function create(string $name): self
    {
        return new self($name);
    }

    public function iteration(): IterationBuilder
    {
        return (function (IterationBuilder $builder) {
            $this->iterations[] = $builder;
            return $builder;
        })(new IterationBuilder($this));
    }

    public function build(): Variant
    {
        $suite = new Suite(
            'testSuite',
            new DateTime()
        );
        $benchmark = new Benchmark($suite, 'testBenchmark');
        $subject = new Subject($benchmark, 'foo');
        $variant = new Variant($subject, new ParameterSet('foo', []), 1, 1, []);
        foreach ($this->iterations as $iteration) {
            $iteration->build($variant);
        }

        return $variant;
    }
}
