<?php

namespace PhpBench\Executor\Unit;

use PhpBench\Executor\ExecutionContext;
use PhpBench\Executor\UnitInterface;
use PhpBench\Executor\StageRenderer;

class CallSubjectUnit implements UnitInterface
{
    public function name(): string
    {
        return 'call_subject';
    }

    /**
     * {@inheritDoc}
     */
    public function start(ExecutionContext $context): array
    {
        $lines = [
            sprintf('$benchmark->%s($parameters);', $context->getMethodName()),
        ];

        return $lines;
    }

    /**
     * {@inheritDoc}
     */
    public function end(ExecutionContext $context): array
    {
        return [
        ];
    }
}

