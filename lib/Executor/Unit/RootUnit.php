<?php

namespace PhpBench\Executor\Unit;

use PhpBench\Executor\ExecutionContext;
use PhpBench\Executor\UnitInterface;
use PhpBench\Executor\StageRenderer;

class RootUnit implements UnitInterface
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
        ];

        if ($this->bootstrap) {
            $lines[] = 'call_user_func(function () {';
            $lines[] = sprintf('  require_once("%s");', $this->bootstrap);
            $lines[] = '});';
        }

        $lines[] = '$parameters = array_map(function (string $serialized) {';
        $lines[] = '    return unserialize($serialized);';
        $lines[] = sprintf('}, %s);', var_export($context->getParameterSet()->toSerializedParameters(), true));

        $lines[] = sprintf('require_once("%s");', $context->getClassPath());
        $lines[] = sprintf('$benchmark = new %s;', $context->getClassName());
        $lines[] = '$results = [];';

        return $lines;
    }

    /**
     * {@inheritDoc}
     */
    public function end(ExecutionContext $context): array
    {
        return [
            '$results["buffer"] = [\'buffer\' => ob_get_contents()];',
            'ob_end_clean();',
            'echo serialize($results);',
            'exit(0);',
        ];
    }
}
