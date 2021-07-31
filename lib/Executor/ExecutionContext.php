<?php

namespace PhpBench\Executor;

use PhpBench\Benchmark\Metadata\SubjectMetadata;
use PhpBench\Model\Iteration;
use PhpBench\Model\ParameterSet;

final class ExecutionContext
{
    /**
     * @var string
     */
    private $className;

    /**
     * @var ParameterSet
     */
    private $parameters;

    /**
     * @var string
     */
    private $classPath;

    /**
     * @var int
     */
    private $iterationIndex;

    /**
     * @var int
     */
    private $warmup;

    /**
     * @var array<string>
     */
    private $afterMethods;

    /**
     * @var array<string>
     */
    private $beforeMethods;

    /**
     * @var int
     */
    private $revolutions;

    /**
     * @var string
     */
    private $methodName;

    /**
     * @var float|null
     */
    private $timeOut;

    /**
     * @var string
     */
    private $parameterSetName;

    /**
     * @param ParameterSet|array<string,mixed>|null $parameters The type array is deprecated and will be removed in 2.0
     * @param string[] $beforeMethods
     * @param string[] $afterMethods
     */
    public function __construct(
        string $className,
        string $classPath,
        string $methodName,
        int $revolutions = 1,
        array $beforeMethods = [],
        array $afterMethods = [],
        $parameters = null,
        int $warmup = 0,
        int $iterationIndex = 0,
        ?float $timeOut = null,
        string $parameterSetName = ''
    ) {
        $this->className = $className;
        $this->classPath = $classPath;
        $this->methodName = $methodName;
        $this->revolutions = $revolutions;
        $this->beforeMethods = $beforeMethods;
        $this->afterMethods = $afterMethods;
        $this->parameters = $parameters instanceof ParameterSet ? $parameters : ParameterSet::fromUnserializedValues('default', $parameters ?? []);
        $this->warmup = $warmup;
        $this->iterationIndex = $iterationIndex;
        $this->timeOut = $timeOut;
        $this->parameterSetName = $parameterSetName;
    }

    public static function fromSubjectMetadataAndIteration(SubjectMetadata $subjectMetadata, Iteration $iteration): self
    {
        return new self(
            $subjectMetadata->getBenchmark()->getClass(),
            $subjectMetadata->getBenchmark()->getPath(),
            $subjectMetadata->getName(),
            $iteration->getVariant()->getRevolutions(),
            $subjectMetadata->getBeforeMethods(),
            $subjectMetadata->getAfterMethods(),
            $iteration->getVariant()->getParameterSet(),
            $iteration->getVariant()->getWarmup() ?: 0,
            $iteration->getIndex(),
            $subjectMetadata->getTimeout(),
            $iteration->getVariant()->getParameterSet()->getName()
        );
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * @deprecated Use getParameterSet will be removed in PHPBench 2.0
     *
     * @return parameters
     */
    public function getParameters(): array
    {
        return $this->parameters->toUnserializedParameters();
    }

    public function getParameterSet(): ParameterSet
    {
        return $this->parameters;
    }

    public function getClassPath(): string
    {
        return $this->classPath;
    }

    public function getIterationIndex(): int
    {
        return $this->iterationIndex;
    }

    public function getWarmup(): int
    {
        return $this->warmup;
    }

    /**
     * @return string[]
     */
    public function getAfterMethods(): array
    {
        return $this->afterMethods;
    }

    /**
     * @return string[]
     */
    public function getBeforeMethods(): array
    {
        return $this->beforeMethods;
    }

    public function getRevolutions(): int
    {
        return $this->revolutions;
    }

    public function getMethodName(): string
    {
        return $this->methodName;
    }

    public function getTimeOut(): ?float
    {
        return $this->timeOut;
    }

    public function getParameterSetName(): string
    {
        return $this->parameterSetName;
    }
}
