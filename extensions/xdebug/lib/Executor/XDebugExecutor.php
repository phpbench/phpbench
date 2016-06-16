<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Extensions\XDebug\Executor;

use PhpBench\Benchmark\Executor\BaseExecutor;
use PhpBench\Benchmark\Remote\Payload;
use PhpBench\Extensions\XDebug\XDebugUtil;
use PhpBench\Model\Iteration;
use PhpBench\Model\Result\MemoryResult;
use PhpBench\Model\Result\TimeResult;
use PhpBench\Model\ResultCollection;
use PhpBench\PhpBench;
use PhpBench\Registry\Config;

class XDebugExecutor extends BaseExecutor
{
    /**
     * {@inheritdoc}
     */
    public function launch(Payload $payload, Iteration $iteration, Config $config)
    {
        $outputDir = $config['output_dir'];
        $callback = $config['callback'];
        $name = XDebugUtil::filenameFromIteration($iteration);

        $phpConfig = [
            'xdebug.profiler_enable' => 1,
            'xdebug.profiler_output_dir' => PhpBench::normalizePath($outputDir),
            'xdebug.profiler_output_name' => $name,
        ];

        if (!extension_loaded('xdebug')) {
            $phpConfig['zend_extension'] = 'xdebug.so';
        }

        $payload->setPhpConfig($phpConfig);
        $result = $payload->launch();

        if (isset($result['buffer']) && $result['buffer']) {
            throw new \RuntimeException(sprintf(
                'Benchmark made some noise: %s',
                $result['buffer']
            ));
        }

        $result = new ResultCollection([
            new TimeResult($result['time']),
            new MemoryResult($result['memory']),
        ]);
        $callback($iteration, $result);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultConfig()
    {
        return [
            'callback' => function () {
            },
            'output_dir' => 'profile',
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
                'callback' => [
                    'type' => null,
                ],
                'output_dir' => [
                    'type' => 'string',
                ],
            ],
        ];
    }
}
