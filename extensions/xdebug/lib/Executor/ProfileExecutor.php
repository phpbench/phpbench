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

namespace PhpBench\Extensions\XDebug\Executor;

use PhpBench\Benchmark\Executor\BaseExecutor;
use PhpBench\Benchmark\Remote\Payload;
use PhpBench\Extensions\XDebug\XDebugUtil;
use PhpBench\Model\Iteration;
use PhpBench\Model\Result\MemoryResult;
use PhpBench\Model\Result\TimeResult;
use PhpBench\PhpBench;
use PhpBench\Registry\Config;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfileExecutor extends BaseExecutor
{
    /**
     * {@inheritdoc}
     */
    public function launch(Payload $payload, Iteration $iteration, Config $config)
    {
        $outputDir = $config['output_dir'];
        $callback = $config['callback'];
        $name = XDebugUtil::filenameFromIteration($iteration, '.cachegrind');

        $phpConfig = [
            'xdebug.profiler_enable' => 1,
            'xdebug.profiler_output_dir' => PhpBench::normalizePath($outputDir),
            'xdebug.profiler_output_name' => $name,
        ];

        $payload->mergePhpConfig($phpConfig);
        $result = $payload->launch();

        if (isset($result['buffer']) && $result['buffer']) {
            throw new \RuntimeException(sprintf(
                'Benchmark made some noise: %s',
                $result['buffer']
            ));
        }

        $callback($iteration, $result);
        $iteration->setResult(new TimeResult($result['time']));
        $iteration->setResult(MemoryResult::fromArray($result['mem']));

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function configure(OptionsResolver $options)
    {
        $options->setDefaults([
            'callback' => function () {
            },
            'output_dir' => 'xdebug',
        ]);
    }
}
