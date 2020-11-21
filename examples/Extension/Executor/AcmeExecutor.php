<?php

namespace PhpBench\Examples\Extension\Executor;

use PhpBench\Executor\BenchmarkExecutorInterface;
use PhpBench\Executor\ExecutionContext;
use PhpBench\Executor\ExecutionResults;
use PhpBench\Model\Result\TimeResult;
use PhpBench\Registry\Config;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AcmeExecutor implements BenchmarkExecutorInterface
{
    private const CONFIG_MICROSECONDS = 'microseconds';

    public function configure(OptionsResolver $options): void
    {
        $options->setDefaults([
            self::CONFIG_MICROSECONDS => 5
        ]);
    }

    public function execute(ExecutionContext $context, Config $config): ExecutionResults
    {
        return ExecutionResults::fromResults(
            new TimeResult($config[self::CONFIG_MICROSECONDS])
        );
    }
}
