<?php

namespace PhpBench\Executor\Method;

use PhpBench\Executor\MethodExecutorContext;
use PhpBench\Executor\MethodExecutorInterface;
use RuntimeException;

final class LocalMethodExecutor implements MethodExecutorInterface
{
    /**
     * @param array<string> $methods
     */
    public function executeMethods(MethodExecutorContext $context, array $methods): void
    {
        $className = $context->getBenchmarkClass();

        if (!class_exists($className)) {
            throw new RuntimeException(sprintf(
                'Class "%s" does not exist',
                $className
            ));
        }

        $class = new $className();

        foreach ($methods as $method) {
            if (!method_exists($class, $method)) {
                throw new RuntimeException(sprintf(
                    'Method "%s" on class "%s" does not exist',
                    $method,
                    $className
                ));
            }

            $class->$method();
        }
    }
}
