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
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * This class generates a benchmarking script and places it in the systems
 * temp. directory and then executes it. The generated script then returns the
 * time taken to execute the benchmark and the memory consumed.
 */
class TraceExecutor extends BaseExecutor
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
    public function __construct(Launcher $launcher, TraceToXmlConverter $converter = null, Filesystem $filesystem = null)
    {
        parent::__construct($launcher);
        $this->filesystem = $filesystem ? $filesystem : new Filesystem();
        $this->converter = $converter ?: new TraceToXmlConverter();
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
            'xdebug.collect_params' => '3',
        ];

        $payload->mergePhpConfig($phpConfig);

        $path = $dir . DIRECTORY_SEPARATOR . $name . '.xt';

        // if the file exists, remove it. XDebug might not be installed
        // on the PHP binary and the file may not be generated. We should
        // fail in such a case and not use the file from a previous run.
        if ($this->filesystem->exists($path)) {
            $this->filesystem->remove($path);
        }

        $result = $payload->launch();

        if (false === $this->filesystem->exists($path)) {
            throw new \RuntimeException(sprintf(
                'Trace file at "%s" was not generated.',
                $path
            ));
        }

        $dom = $this->converter->convert($path);

        $subject = $iteration->getVariant()->getSubject();
        $class = $subject->getBenchmark()->getClass();

        // remove leading slash from class name for matching
        // the class in the trace.
        if (substr($class, 0, 1) == '\\') {
            $class = substr($class, 1);
        }

        // extract only the timings for the benchmark class, ignore the bootstrapping
        $selector = '//entry[@function="' . $class . '->' . $subject->getName() . '"]';

        // calculate stats from the trace
        $time = (int) ($dom->evaluate(sprintf(
            'number(%s/@end-time) - number(%s/@start-time)',
            $selector, $selector
        )) * 1E6);

        $memory = (int) $dom->evaluate(sprintf(
            'number(%s/@end-memory) - number(%s/@start-memory)',
            $selector, $selector
        ));
        $funcCalls = (int) $dom->evaluate('count(' . $selector . '//*)');

        $iteration->setResult(new TimeResult($result['time']));
        $iteration->setResult(MemoryResult::fromArray($result['mem']));
        $iteration->setResult(new XDebugTraceResult($time, $memory, $funcCalls, $dom));
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
