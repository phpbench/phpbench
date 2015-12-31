<?php

namespace PhpBench\Extensions\XDebug\Executor;

use PhpBench\Benchmark\Executor\BaseExecutor;
use PhpBench\Benchmark\Remote\Payload;
use PhpBench\Benchmark\Iteration;
use PhpBench\Benchmark\IterationResult;
use PhpBench\Extensions\XDebug\XDebugUtil;

class XDebugExecutor extends BaseExecutor
{
    /**
     * {@inheritdoc}
     */
    public function launch(Payload $payload, Iteration $iteration, array $options = array())
    {
        $outputDir = $options['output_dir'];
        $callback = $options['callback'];
        $name = XDebugUtil::filenameFromIteration($iteration);

        $phpConfig = array(
            'xdebug.profiler_enable' => 1,
            'xdebug.profiler_output_dir' => $outputDir,
            'xdebug.profiler_output_name' => $name,
        );

        $payload->setPhpConfig($phpConfig);
        $result = $payload->launch();


        if (isset($result['buffer']) && $result['buffer']) {
            throw new \RuntimeException(sprintf(
                'Benchmark made some noise: %s',
                $result['buffer']
            ));
        }

        $result = new IterationResult($result['time'], $result['memory']);
        $callback($iteration, $result);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultConfig()
    {
        return array(
            'callback' => array(),
        );
    }
}
