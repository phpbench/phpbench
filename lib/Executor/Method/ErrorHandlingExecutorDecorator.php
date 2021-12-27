<?php

namespace PhpBench\Executor\Method;

use PhpBench\Executor\Exception\ExecutionError;
use PhpBench\Executor\MethodExecutorContext;
use PhpBench\Executor\MethodExecutorInterface;
use Throwable;

class ErrorHandlingExecutorDecorator implements MethodExecutorInterface
{
    /**
     * @var MethodExecutorInterface
     */
    private $executor;

    public function __construct(MethodExecutorInterface $executor)
    {
        $this->executor = $executor;
    }

    public function executeMethods(MethodExecutorContext $context, array $methods): void
    {
        try {
            $this->executor->executeMethods($context, $methods);
        } catch (Throwable $throwable) {
            throw new ExecutionError(sprintf(
                'Could not execute method(s) "%s" on "%s"',
                implode('", "', $methods),
                $context->getBenchmarkClass()
            ));
        }
    }
}
