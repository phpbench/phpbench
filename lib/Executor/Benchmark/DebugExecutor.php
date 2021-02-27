<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace PhpBench\Executor\Benchmark;

use PhpBench\Executor\BenchmarkExecutorInterface;
use PhpBench\Executor\ExecutionContext;
use PhpBench\Executor\ExecutionResults;
use PhpBench\Model\Result\MemoryResult;
use PhpBench\Model\Result\TimeResult;
use PhpBench\Registry\Config;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * This executor for testing purposes. It always returns constant times, it
 * does not actually execute any benchmarking.
 */
class DebugExecutor implements BenchmarkExecutorInterface
{
    private $variantTimes = [];
    private $index = 0;

    /**
     * {@inheritdoc}
     */
    public function execute(ExecutionContext $context, Config $config): ExecutionResults
    {
        $results = ExecutionResults::new();

        // add 100 bytes of memory.
        $memory = 100;
        $results->add(new MemoryResult($memory, $memory, $memory));

        if (!$config['times']) {
            $results->add(new TimeResult(0, $context->getRevolutions()));

            return $results;
        }

        $contextHash = spl_object_hash($context);

        if (!isset($this->variantTimes[$contextHash])) {
            $this->variantTimes[$contextHash] = $config['times'];
        }

        if (!isset($this->variantTimes[$contextHash][$this->index])) {
            $this->index = 0;
        }

        $time = $this->variantTimes[$contextHash][$this->index];
        $this->index++;

        if ($config['spread']) {
            $index = $context->getIterationIndex() % count($config['spread']);
            $spreadDiff = $config['spread'][$index];
            $time = $time + $spreadDiff;
        }

        $results->add(new TimeResult($time, $context->getRevolutions()));

        return $results;
    }

    /**
     * {@inheritdoc}
     */
    public function configure(OptionsResolver $options): void
    {
        $options->setDefaults([
            'times' => [10],
            'spread' => [0],
            'memories' => null,
        ]);

        $options->setAllowedTypes('times', 'array');
        $options->setAllowedTypes('spread', 'array');
        $options->setAllowedTypes('memories', ['null', 'array']);
    }
}
