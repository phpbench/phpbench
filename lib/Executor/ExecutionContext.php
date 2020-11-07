<?php

namespace PhpBench\Executor;

use PhpBench\Benchmark\Metadata\SubjectMetadata;
use PhpBench\Model\Iteration;

final class ExecutionContext
{
    /**
     * @var string
     */
    private $className;

    /**
     * @var array<string,mixed>
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

    public function __construct(
        string $className,
        string $classPath,
        string $methodName,
        int $revolutions = 1,
        array $beforeMethods = [],
        array $afterMethods = [],
        array $parameters = [],
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
        $this->parameters = $parameters;
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
            $iteration->getVariant()->getParameterSet()->getArrayCopy(),
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

    public function getParameters(): array
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

    public function getAfterMethods(): array
    {
        return $this->afterMethods;
    }

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
