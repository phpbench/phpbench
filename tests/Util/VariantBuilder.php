<?php

namespace PhpBench\Tests\Util;

use DateTime;
use PhpBench\Model\Benchmark;
use PhpBench\Model\Error;
use PhpBench\Model\ParameterSet;
use PhpBench\Model\Subject;
use PhpBench\Model\Suite;
use PhpBench\Model\Variant;
use RuntimeException;

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

    /**
     * @var SubjectBuilder|null
     */
    private $subjectBuilder;

    /**
     * @var string
     */
    private $name;

    /**
     * @var Error[]
     */
    private $errors;

    public function __construct(?SubjectBuilder $subjectBuilder, string $name)
    {
        $this->subjectBuilder = $subjectBuilder;
        $this->name = $name;
    }

    public static function create(string $name = 'foo'): self
    {
        return new self(null, $name);
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

    public function build(?Subject $subject = null): Variant
    {
        if (null === $subject) {
            $suite = new Suite(
                'testSuite',
                new DateTime()
            );
            $benchmark = new Benchmark($suite, 'testBenchmark');
            $subject = new Subject($benchmark, 'foo');
        }
        $variant = new Variant($subject, ParameterSet::fromSerializedParameters($this->name, []), $this->revs, 1, []);

        foreach ($this->iterations as $iteration) {
            $iteration->build($variant);
        }

        $variant->computeStats();

        if ($this->errors) {
            $variant->createErrorStack($this->errors);
        }

        return $variant;
    }

    public function end(): SubjectBuilder
    {
        if (null === $this->subjectBuilder) {
            throw new RuntimeException(
                'This variant builder was not created by a subject builder, end() cannot return anything'
            );
        }

        return $this->subjectBuilder;
    }

    public static function forSubjectBuilder(SubjectBuilder $subjectBuilder, string $name): self
    {
        return new self($subjectBuilder, $name);
    }

    public function withError(Error $error): self
    {
        $this->errors[] = $error;

        return $this;
    }
}
