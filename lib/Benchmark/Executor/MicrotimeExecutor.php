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

use PhpBench\Benchmark\Iteration;
use PhpBench\Benchmark\IterationResult;
use PhpBench\Benchmark\Remote\Payload;

/**
 * This class generates a benchmarking script and places it in the systems
 * temp. directory and then executes it. The generated script then returns the
 * time taken to execute the benchmark and the memory consumed.
 */
class MicrotimeExecutor extends BaseExecutor
{
    /**
     * {@inheritdoc}
     */
    public function launch(Payload $payload, Iteration $iteration, array $options = array())
    {
        $phpConfig = array(
            'max_execution_time' => 0,
        );

        $payload->setPhpConfig($phpConfig);
        $result = $payload->launch();

        if (isset($result['buffer']) && $result['buffer']) {
            throw new \RuntimeException(sprintf(
                'Benchmark made some noise: %s',
                $result['buffer']
            ));
        }

        return new IterationResult($result['time'], $result['memory']);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultConfig()
    {
        return array(
            'php_config' => array(),
        );
    }
}
