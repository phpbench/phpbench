<?php

namespace PhpBench\Executor\Method;

use PhpBench\Benchmark\Metadata\BenchmarkMetadata;
use PhpBench\Benchmark\Remote\Launcher;
use PhpBench\Executor\MethodExecutorInterface;

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
    public function executeMethods(BenchmarkMetadata $benchmark, array $methods): void
    {
        $tokens = [
            'class' => $benchmark->getClass(),
            'file' => $benchmark->getPath(),
            'methods' => var_export($methods, true),
        ];

        $payload = $this->launcher->payload(__DIR__ . '/template/execute_static_methods.template', $tokens);
        $payload->launch();
    }
}
