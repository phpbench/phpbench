<?php

namespace PhpBench\Executor\Unit;

use PhpBench\Executor\ExecutionContext;
use PhpBench\Executor\UnitInterface;

class RevLoopUnit implements UnitInterface
{
    public function name(): string
    {
        return 'rev_loop';
    }

    /**
     * {@inheritDoc}
     */
    public function start(ExecutionContext $context): array
    {
        return [
            sprintf('for ($rev = 0; $rev < %d; $rev++) {', $context->getRevolutions()),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function end(ExecutionContext $context): array
    {
        return [
            '}'
        ];
    }
}
