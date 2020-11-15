<?php

namespace PhpBench\Executor\Method;

use PhpBench\Benchmark\Metadata\BenchmarkMetadata;
use PhpBench\Executor\MethodExecutorInterface;
use RuntimeException;

class LocalMethodExecutor implements MethodExecutorInterface
{
    /**
     * @param array<string> $methods
     */
    public function executeMethods(BenchmarkMetadata $benchmark, array $methods): void
    {
        $class = $benchmark->getClass();

        if (!class_exists($class)) {
            throw new RuntimeException(sprintf(
                'Class "%s" does not exist', $class
            ));
        }

        $class = new $class;

        foreach ($methods as $method) {
            if (!method_exists($class, $method)) {
                throw new RuntimeException(sprintf(
                    'Method "%s" on class "%s" does not exist', $method, $class
                ));
            }

            $class->$method();
        }
    }
}
