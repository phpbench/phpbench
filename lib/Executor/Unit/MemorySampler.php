<?php

namespace PhpBench\Executor\Unit;

use PhpBench\Executor\ExecutionContext;
use PhpBench\Executor\UnitInterface;

class MemorySampler implements UnitInterface
{
    public function name(): string
    {
        return 'memory_sampler';
    }

    /**
     * {@inheritDoc}
     */
    public function start(ExecutionContext $context): array
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function end(ExecutionContext $context): array
    {
        return [
            '$results[\'mem\'] = [',
            '    \'peak\' => memory_get_peak_usage(),',
            '    \'final\' => memory_get_usage(),',
            '    \'real\' => memory_get_usage(true),',
            '];',
        ];
    }
}
