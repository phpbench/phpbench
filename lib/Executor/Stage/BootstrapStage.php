<?php

namespace PhpBench\Executor\Stage;

use PhpBench\Executor\ExecutionContext;
use PhpBench\Executor\StageRenderer;

class BootstrapStage
{
    /**
     * @var string|null
     */
    private $bootstrap;

    public function __construct(?string $bootstrap)
    {
        $this->bootstrap = $bootstrap;
    }

    /**
     * @return string[]
     */
    public function render(ExecutionContext $context, StageRenderer $renderer): array
    {
        $lines = [
            'gc_disable();',
            'ob_start();',
            sprintf('$class = %s::class;', $context->getClassName()),
        ];

        if ($this->bootstrap) {
            $lines[] = 'call_user_func(function () {';
            $lines[] = sprintf('  require_once(%s);', $this->bootstrap);
            $lines[] = '});';
        }

        $lines[] = sprintf('require_once("%s")', $context->getClassPath());

        return $lines;
    }
}
