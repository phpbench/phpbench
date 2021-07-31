<?php

namespace PhpBench\Tests\Util;

use DateTime;
use PhpBench\Model\Benchmark;
use PhpBench\Model\ParameterSet;
use PhpBench\Model\Subject;
use PhpBench\Model\Suite;
use PhpBench\Model\Variant;

final class VariantBuilder
{
    /**
     * @var IterationBuilder[]
     */
    private $iterations = [];

    /**
     * @var int
     */
    private $revs = 1;


    public function __construct()
    {
    }

    public static function create(): self
    {
        return new self();
    }

    public function setRevs(int $revs): self
    {
        $this->revs = $revs;

        return $this;
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
        $variant = new Variant($subject, ParameterSet::fromSerializedParameters('foo', []), $this->revs, 1, []);

        foreach ($this->iterations as $iteration) {
            $iteration->build($variant);
        }

        return $variant;
    }
}
