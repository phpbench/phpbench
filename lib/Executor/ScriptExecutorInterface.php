<?php

namespace PhpBench\Executor;

interface ScriptExecutorInterface
{
    public function execute(string $script): array;
}
