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

use PhpBench\Benchmark\Metadata\SubjectMetadata;
use PhpBench\Benchmark\Remote\Launcher;
use PhpBench\Benchmark\ExecutorInterface;
use PhpBench\Extensions\XDebug\Converter\TraceToXmlConverter;

/**
 * This class generates a benchmarking script and places it in the systems
 * temp. directory and then executes it. The generated script then returns the
 * time taken to execute the benchmark and the memory consumed.
 */
class XDebugTraceExecutor implements ExecutorInterface
{
    /**
     * @var Launcher
     */
    private $launcher;

    /**
     * @var string
     */
    private $defaultDir;

    /**
     * @var TraceToXmlConverter
     */
    private $converter;

    /**
     * @param Launcher $launcher
     * @param string $configPath
     * @param string $bootstrap
     */
    public function __construct(Launcher $launcher)
    {
        $this->launcher = $launcher;
        $this->converter = new TraceToXmlConverter();

        // FIXME: Use sys temp dir when you have internet to find out the syntax
        $this->defaultDir = '/tmp/phpbench-xdebug';
    }

    /**
     * {@inheritDoc}
     */
    public function execute(SubjectMetadata $subject, $revolutions = 1, array $parameters = array(), $options = array())
    {
        static $index = 0;
        $benchmark = $subject->getBenchmarkMetadata();
        $name = str_replace('\\', '_', $benchmark->getClass()) . '::' . $subject->getName() . '.' . $index++ . '.trace';
        $dir = $options['dir'];

        if (!file_exists($dir)) {
            // FIXME: Recursively create
            mkdir($dir);
        }

        if (!file_exists($dir)) {
            throw new \RuntimeException(sprintf(
                'Directory "%s" does not exist and I could not create it.',
                $dir
            ));
        }

        $path = $dir . DIRECTORY_SEPARATOR . $name . '.xt';

        $this->launcher->launch(
            __DIR__ . '/template/xdebug.template',
            array(
                'class' => $benchmark->getClass(),
                'file' => $benchmark->getPath(),
                'subject' => $subject->getName(),
                'revolutions' => $revolutions,
                'beforeMethods' => var_export($subject->getBeforeMethods(), true),
                'afterMethods' => var_export($subject->getAfterMethods(), true),
                'parameters' => var_export($parameters, true),
            ), array(
                'xdebug.trace_output_name' => $name,
                'xdebug.trace_output_dir' => $dir,
                'xdebug.trace_format' => '1',
                'xdebug.auto_trace' => '1',
                'xdebug.coverage_enable' => '0',
            )
        );

        if (!file_exists($path)) {
            throw new \RuntimeException(sprintf(
                'Trace file at "%s" was not generated.',
                $path
            ));
        }

        $dom = $this->converter->convert($path);
        unlink($path);

        $class = $benchmark->getClass();
        if (substr($class, 0, 1) == '\\') {
            $class = substr($class, 1);
        }

        // extract only the timings for the benchmark class, ignore the bootstrapping
        $selector = '//entry[@function="' . $class . '->' . $subject->getName() . '"]';
        $time = $dom->xpath()->evaluate('sum( ' . $selector . '/@end-time) - sum(' . $selector . '/@start-time)') * 1000000;
        $memory = $dom->xpath()->evaluate('sum( ' . $selector . '/@end-memory) - sum(' . $selector . '/@start-memory)');
        $funcCalls = $dom->xpath()->evaluate('count(' . $selector . '//*)');

        return array(
            'time' => $time,
            'memory' => $memory,
            'calls' => $funcCalls,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getSchema()
    {
        return array(
            'type' => 'object',
            'additionalProperties' => false,
            'properties' => array(
                'dir' => array(
                    'type' => 'string',
                ),
            ),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultConfig()
    {
        return array(
            'dir' => $this->defaultDir,
        );
    }
}

