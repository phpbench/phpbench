<?php

namespace PhpBench\Executor;

use PhpBench\Benchmark\Metadata\SubjectMetadata;
use PhpBench\Model\Iteration;
use PhpBench\Model\ParameterSet;

final class ExecutionContext
{
    private readonly ParameterSet $parameters;

    /**
     * @param ParameterSet|array<string,mixed>|null $parameters The type array is deprecated and will be removed in 2.0
     * @param string[] $beforeMethods
     * @param string[] $afterMethods
     */
    public function __construct(
        private readonly string $className,
        private readonly string $classPath,
        private readonly string $methodName,
        private readonly int $revolutions = 1,
        private readonly array $beforeMethods = [],
        private readonly array $afterMethods = [],
        $parameters = null,
        private readonly int $warmup = 0,
        private readonly int $iterationIndex = 0,
        private readonly ?float $timeOut = null,
        private readonly string $parameterSetName = ''
    ) {
        $this->parameters = $parameters instanceof ParameterSet ? $parameters : ParameterSet::fromUnserializedValues('default', $parameters ?? []);
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
