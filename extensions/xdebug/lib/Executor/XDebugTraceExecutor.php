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
use PhpBench\Benchmark\Remote\Launcher;
use PhpBench\Benchmark\Remote\Payload;
use PhpBench\Extensions\XDebug\Converter\TraceToXmlConverter;
use PhpBench\Extensions\XDebug\Result\XDebugTraceResult;
use PhpBench\Extensions\XDebug\XDebugUtil;
use PhpBench\Model\Iteration;
use PhpBench\Model\Result\MemoryResult;
use PhpBench\Model\Result\TimeResult;
use PhpBench\Registry\Config;
use Symfony\Component\Filesystem\Filesystem;

/**
 * This class generates a benchmarking script and places it in the systems
 * temp. directory and then executes it. The generated script then returns the
 * time taken to execute the benchmark and the memory consumed.
 */
class XDebugTraceExecutor extends BaseExecutor
{
    /**
     * @var TraceToXmlConverter
     */
    private $converter;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @param Launcher $launcher
     * @param string $configPath
     * @param string $bootstrap
     */
    public function __construct(Launcher $launcher, Filesystem $filesystem)
    {
        parent::__construct($launcher);
        $this->converter = new TraceToXmlConverter();
        $this->filesystem = $filesystem;
    }

    /**
     * {@inheritdoc}
     */
    public function launch(Payload $payload, Iteration $iteration, Config $config)
    {
        $name = XDebugUtil::filenameFromIteration($iteration);
        $dir = $config['output_dir'];

        $phpConfig = [
            'xdebug.trace_output_name' => $name,
            'xdebug.trace_output_dir' => $dir,
            'xdebug.trace_format' => '1',
            'xdebug.auto_trace' => '1',
            'xdebug.coverage_enable' => '0',
        ];

        $payload->mergePhpConfig($phpConfig);

        $path = $dir . DIRECTORY_SEPARATOR . $name . '.xt';
        $result = $payload->launch();

        if (false === $this->filesystem->exists($path)) {
            throw new \RuntimeException(sprintf(
                'Trace file at "%s" was not generated. Is XDebug enabled in the benchmarking environment',
                $path
            ));
        }

        $dom = $this->converter->convert($path);

        $subject = $iteration->getVariant()->getSubject();
        $class = $subject->getBenchmark()->getClass();
        if (substr($class, 0, 1) == '\\') {
            $class = substr($class, 1);
        }

        // extract only the timings for the benchmark class, ignore the bootstrapping
        $selector = '//entry[@function="' . $class . '->' . $subject->getName() . '"]';

        // calculate stats from the trace
        $time = (int) ($dom->evaluate(sprintf(
            'sum(%s/@end-time) - sum(/@start-time)',
            $selector, $selector
        )) * 1E6);

        $memory = (int) $dom->evaluate(sprintf(
            'sum(%s/@end-memory) - sum(/@start-memory)',
            $selector, $selector
        ));
        $funcCalls = (int) $dom->evaluate('count(' . $selector . '//*)');

        $iteration->setResult(new TimeResult($result['time']));
        $iteration->setResult(MemoryResult::fromArray($result['mem']));
        $iteration->setResult(new XDebugTraceResult($time, $memory, $funcCalls));
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
