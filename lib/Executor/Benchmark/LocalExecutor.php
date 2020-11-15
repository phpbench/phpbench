<?php

namespace PhpBench\Executor\Benchmark;

use PhpBench\Executor\BenchmarkExecutorInterface;
use PhpBench\Executor\Exception\ExecutionError;
use PhpBench\Executor\ExecutionContext;
use PhpBench\Executor\ExecutionResults;
use PhpBench\Model\Result\TimeResult;
use PhpBench\Registry\Config;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LocalExecutor implements BenchmarkExecutorInterface
{
    /**
     * {@inheritDoc}
     */
    public function configure(OptionsResolver $options): void
    {
    }

    public function execute(ExecutionContext $context, Config $config): ExecutionResults
    {
        $benchmark = $this->createBenchmark($context);

        $methodName = $context->getMethodName();
        $parameters = $context->getParameters();

        foreach ($context->getBeforeMethods() as $afterMethod) {
            $benchmark->$afterMethod($parameters);
        }

        for ($i = 0; $i < $context->getWarmup() ?: 0; $i++) {
            $benchmark->$methodName($parameters);
        }

        $start = microtime(true);

        for ($i = 0; $i < $context->getRevolutions(); $i++) {
            $benchmark->$methodName($parameters);
        }

        $end = microtime(true);

        foreach ($context->getAfterMethods() as $afterMethod) {
            $benchmark->$afterMethod($parameters);
        }

        return ExecutionResults::fromResults(
            new TimeResult((int)(($end - $start) * 1E6))
        );
    }

    /**
     * @return object
     */
    private function createBenchmark(ExecutionContext $context)
    {
        $className = $context->getClassName();
        
        if (!class_exists($className)) {
            throw new ExecutionError(sprintf(
                'Benchmark class "%s" does not exist', $className
            ));
        }
        
        return new $className;
    }
}
