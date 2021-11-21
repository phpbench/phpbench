<?php

namespace PhpBench\Executor\Stage;

use PhpBench\Executor\ExecutionContext;
use PhpBench\Executor\StageInterface;

class TestStage implements StageInterface
{
    /**
     * @var string
     */
    private $start;

    /**
     * @var string
     */
    private $end;

    /**
     * @var string
     */
    private $name;

    public function __construct(string $name, string $start, string $end)
    {
        $this->start = $start;
        $this->end = $end;
        $this->name = $name;
    }

    public function name(): string
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     */
    public function start(ExecutionContext $context): array
    {
        return [$this->start];
    }

    /**
     * {@inheritDoc}
     */
    public function end(ExecutionContext $context): array
    {
        return [$this->end];
    }
}
