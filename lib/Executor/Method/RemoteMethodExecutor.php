<?php

namespace PhpBench\Executor\Method;

use PhpBench\Executor\MethodExecutorContext;
use PhpBench\Executor\MethodExecutorInterface;
use PhpBench\Remote\Launcher;

class RemoteMethodExecutor implements MethodExecutorInterface
{
    /**
     * @var Launcher
     */
    private $launcher;

    public function __construct(Launcher $launcher)
    {
        $this->launcher = $launcher;
    }

    /**
     * {@inheritdoc}
     */
    public function executeMethods(MethodExecutorContext $context, array $methods): void
    {
        $tokens = [
            'class' => $context->getBenchmarkClass(),
            'file' => $context->getBenchmarkPath(),
            'methods' => var_export($methods, true),
        ];

        $payload = $this->launcher->payload(__DIR__ . '/template/execute_static_methods.template', $tokens);
        $payload->launch();
    }
}
