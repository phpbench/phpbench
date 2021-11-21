<?php

namespace PhpBench\Tests\Util;

use DateTime;
use PhpBench\Model\Benchmark;
use PhpBench\Model\Error;
use PhpBench\Model\ParameterSet;
use PhpBench\Model\Result\TimeResult;
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
     * @var string|null
     */
    private $name;

    /**
     * @var Error[]
     */
    private $errors;

    /**
     * @var ParameterSet
     */
    private $parameterSet = null;

    /**
     * @param string $name @deprecated Variants are not named, and this was used as the parameter set name.
     */
    public function __construct(?SubjectBuilder $subjectBuilder, ?string $name = null)
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

    public function addIterationWithTimeResult(int $netTime, int $revs): VariantBuilder
    {
        $this->iteration()->setResult(new TimeResult($netTime, $revs));

        return $this;
    }

    public function iteration(): IterationBuilder
    {
        return (function (IterationBuilder $builder) {
            $this->iterations[] = $builder;

            return $builder;
        })(new IterationBuilder($this));
    }

    /**
     * @param array<string,mixed> $parameters
     */
    public function withParameterSet(string $name, array $parameters): VariantBuilder
    {
        $this->parameterSet = ParameterSet::fromUnserializedValues($name, $parameters);

        return $this;
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
        $variant = new Variant(
            $subject,
            $this->parameterSet ?? ParameterSet::fromSerializedParameters($this->name ?? '0', []),
            $this->revs,
            1,
            []
        );

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

    public static function forSubjectBuilder(SubjectBuilder $subjectBuilder, ?string $name = null): self
    {
        return new self($subjectBuilder, $name);
    }

    public function withError(Error $error): self
    {
        $this->errors[] = $error;

        return $this;
    }
}
