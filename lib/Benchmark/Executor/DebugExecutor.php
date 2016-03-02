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
use PhpBench\Model\IterationResult;
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
        $memory = 100;
        $collectionHash = spl_object_hash($iteration->getVariant());

        if (!$config['times']) {
            return new IterationResult(0, $memory);
        }

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

        return new IterationResult($time, $memory);
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
