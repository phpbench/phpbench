<?php

namespace PhpBench\Executor\Unit;

use PhpBench\Executor\ExecutionContext;
use PhpBench\Executor\UnitInterface;

class AfterMethodsUnit implements UnitInterface
{
    public function name(): string
    {
        return 'after_methods';
    }

    /**
     * {@inheritDoc}
     */
    public function start(ExecutionContext $context): array
    {
        $lines = [];

        foreach ($context->getAfterMethods() as $afterMethod) {
            $lines[] = sprintf('$benchmark->%s($parameters);', $afterMethod);
        }

        return $lines;
    }

    /**
     * {@inheritDoc}
     */
    public function end(ExecutionContext $context): array
    {
        return [];
    }
}
