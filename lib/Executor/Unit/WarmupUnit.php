<?php

namespace PhpBench\Executor\Unit;

use PhpBench\Executor\ExecutionContext;
use PhpBench\Executor\UnitInterface;

class WarmupUnit implements UnitInterface
{
    public function name(): string
    {
        return 'warmup';
    }

    /**
     * {@inheritDoc}
     */
    public function start(ExecutionContext $context): array
    {
        if (0 === $context->getWarmup()) {
            return [];
        }

        return [
            sprintf('for ($warmup = 0; $warmup < %d; $warmup++) {', $context->getWarmup()),
            sprintf('    $benchmark->%s($parameters);', $context->getMethodName()),
            '}'
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function end(ExecutionContext $context): array
    {
        return [];
    }
}
