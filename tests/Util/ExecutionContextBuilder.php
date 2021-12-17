<?php

namespace PhpBench\Tests\Util;

use PhpBench\Executor\ExecutionContext;
use PhpBench\Model\ParameterSet;

final class ExecutionContextBuilder
{
    /**
     * @var string
     */
    private $class;
    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $methodName;

    /**
     * @var array
     */
    private $parameters = [];

    /**
     * @var array
     */
    private $beforeMethods = [];

    /**
     * @var array
     */
    private $afterMethods = [];

    /**
     * @var int
     */
    private $warmup = 0;

    public static function create(): self
    {
        return new self();
    }

    public function withBenchmarkClass(string $class): self
    {
        $this->class = $class;
        return $this;
    }

    public function withBenchmarkPath(string $path): self
    {
        $this->path = $path;
        return $this;
    }

    public function withMethodName(string $methodName): self
    {
        $this->methodName = $methodName;
        return $this;
    }

    public function build(): ExecutionContext
    {
        return new ExecutionContext(
            $this->class,
            $this->path,
            $this->methodName,
            1,
            $this->beforeMethods,
            $this->afterMethods,
            ParameterSet::fromUnserializedValues('one', $this->parameters),
            $this->warmup,
        );
    }

    public function withParameters(array $parameters): self
    {
        $this->parameters=  $parameters;
return $this;
    }

    public function withBeforeMethods(array $beforeMethods): self
    {
        $this->beforeMethods = $beforeMethods;
        return $this;
    }

    public function withAfterMethods(array $afterMethods): self
    {
        $this->afterMethods = $afterMethods;
        return $this;
    }

    public function withWarmup(int $warmup): self
    {
        $this->warmup = $warmup;
        return $this;
    }
}
