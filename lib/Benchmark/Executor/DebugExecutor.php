<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
    private $collectionTimes = [];

    /**
     * {@inheritdoc}
     */
    public function execute(SubjectMetadata $subjectMetadata, Iteration $iteration, Config $config)
    {
        // add 100 bytes of memory.
        $memory = 100;
        $iteration->addResult(new MemoryResult($memory, $memory, $memory));

        if (!$config['times']) {
            $iteration->addResult(new TimeResult(0));

            return;
        }

        $collectionHash = spl_object_hash($iteration->getVariant());

        if (isset($this->collectionTimes[$collectionHash])) {
            $time = $this->collectionTimes[$collectionHash];
        } else {
            $index = count($this->collectionTimes) % count($config['times']);
            $time = $config['times'][$index];
            $this->collectionTimes[$collectionHash] = $time;
        }

        if ($config['spread']) {
            $index = $iteration->getIndex() % count($config['spread']);
            $spreadDiff = $config['spread'][$index];
            $time = $time + $spreadDiff;
        }

        $iteration->addResult(new TimeResult($time));
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
