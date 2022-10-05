<?php

namespace PhpBench\Executor;

interface UnitInterface
{
    public function name(): string;

    /**
     * @return string[]
     */
    public function start(ExecutionContext $context): array;

    /**
     * @return string[]
     */
    public function end(ExecutionContext $context): array;
}
