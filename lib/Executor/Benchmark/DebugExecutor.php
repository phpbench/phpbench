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

use PhpBench\Benchmark\Metadata\SubjectMetadata;
use PhpBench\Executor\BenchmarkExecutorInterface;
use PhpBench\Model\Iteration;
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
    public function execute(SubjectMetadata $subjectMetadata, Iteration $iteration, Config $config): void
    {
        // add 100 bytes of memory.
        $memory = 100;
        $iteration->setResult(new MemoryResult($memory, $memory, $memory));

        if (!$config['times']) {
            $iteration->setResult(new TimeResult(0));

            return;
        }

        $variantHash = spl_object_hash($iteration->getVariant());

        if (!isset($this->variantTimes[$variantHash])) {
            $this->variantTimes[$variantHash] = $config['times'];
        }

        if (!isset($this->variantTimes[$variantHash][$this->index])) {
            $this->index = 0;
        }

        $time = $this->variantTimes[$variantHash][$this->index];
        $this->index++;

        if ($config['spread']) {
            $index = $iteration->getIndex() % count($config['spread']);
            $spreadDiff = $config['spread'][$index];
            $time = $time + $spreadDiff;
        }

        $iteration->setResult(new TimeResult($time));
    }

    /**
     * {@inheritdoc}
     */
    public function configure(OptionsResolver $options)
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
