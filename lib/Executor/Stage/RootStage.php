<?php

namespace PhpBench\Executor\Stage;

use PhpBench\Executor\ExecutionContext;
use PhpBench\Executor\StageInterface;
use PhpBench\Executor\StageRenderer;

class RootStage implements StageInterface
{
    /**
     * @var string|null
     */
    private $bootstrap;

    public function __construct(?string $bootstrap)
    {
        $this->bootstrap = $bootstrap;
    }

    public function name(): string
    {
        return 'root';
    }

    /**
     * {@inheritDoc}
     */
    public function start(ExecutionContext $context): array
    {
        $lines = [
            'gc_disable();',
            'ob_start();',
            sprintf('$class = %s::class;', $context->getClassName()),
        ];

        if ($this->bootstrap) {
            $lines[] = 'call_user_func(function () {';
            $lines[] = sprintf('  require_once("%s");', $this->bootstrap);
            $lines[] = '});';
        }

        $lines[] = sprintf('require_once("%s");', $context->getClassPath());
        $lines[] = '$results = [];';

        return $lines;
    }

    /**
     * {@inheritDoc}
     */
    public function end(ExecutionContext $context): array
    {
        return [
            '$results["buffer"] = ob_get_contents();',
            'ob_end_clean();',
            'echo serialize($results);',
            'exit(0);',
        ];
    }
}
