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

namespace PhpBench\Benchmark\Executor;

use PhpBench\Benchmark\Metadata\SubjectMetadata;
use PhpBench\Benchmark\Remote\Payload;
use PhpBench\Model\Iteration;
use PhpBench\Model\Result\MemoryResult;
use PhpBench\Model\Result\TimeResult;
use PhpBench\Registry\Config;

/**
 * This executor for testing purposes. It always returns constant times, it
 * does not actually execute any benchmarking.
 */
class DebugExecutor extends BaseExecutor
{
    private $variantTimes = [];
    private $index = 0;

    /**
     * {@inheritdoc}
     */
    public function execute(SubjectMetadata $subjectMetadata, Iteration $iteration, Config $config)
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
     * TODO: Decouple the logic from the base class.
     * {@inheritdoc}
     */
    protected function launch(Payload $payload, Iteration $iteration, Config $config)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultConfig()
    {
        return [
            'times' => [10],
            'spread' => [0],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getSchema()
    {
        return [
            'type' => 'object',
            'additionalProperties' => false,
            'properties' => [
                'times' => [
                    'type' => 'array',
                ],
                'spread' => [
                    'type' => 'array',
                ],
                'memories' => [
                    'type' => 'array',
                ],
            ],
        ];
    }
}
