<?php

namespace PhpBench\Executor\Unit;

use PhpBench\Executor\ExecutionContext;
use PhpBench\Executor\UnitInterface;

class HrtimeSampler implements UnitInterface
{
    /**
     * @var string
     */
    private $varName;

    public function name(): string
    {
        return 'hrtime_sampler';
    }

    /**
     * {@inheritDoc}
     */
    public function start(ExecutionContext $context): array
    {
        $this->varName = sprintf('hrtime_%s', uniqid());
        return [
            sprintf('$%s = hrtime(true);', $this->varName),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function end(ExecutionContext $context): array
    {
        $this->varName = sprintf('hrtime_%s', uniqid());
        return [
            sprintf('$%s_time = hrtime(true) - $%s;', $this->varName, $this->varName),
            sprintf('$results[\'hrtime\'] = $%s_time;', $this->varName),
        ];
    }
}
