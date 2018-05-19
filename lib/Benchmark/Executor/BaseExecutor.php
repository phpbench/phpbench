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

use PhpBench\Benchmark\ExecutorInterface;
use PhpBench\Benchmark\Metadata\BenchmarkMetadata;
use PhpBench\Benchmark\Metadata\SubjectMetadata;
use PhpBench\Benchmark\Remote\Launcher;
use PhpBench\Benchmark\Remote\Payload;
use PhpBench\Model\Iteration;
use PhpBench\Registry\Config;

/**
 * This is a bad class.
 *
 * The executor bundles both the methods for executing before and after and the
 * benchmark iteration executor.
 *
 * The standard use case for an Executor is to execute or obtain the iteration
 * measurements in a different way (e.g. xdebug, blackfire), the executeMethods logic
 * has nothing to do with this and so this awkward base class is required.
 *
 * To be refactored...
 */
abstract class BaseExecutor implements ExecutorInterface
{
    /**
     * @var Launcher
     */
    private $launcher;

    /**
     * @param Launcher $launcher
     * @param string $configPath
     * @param string $bootstrap
     */
    public function __construct(Launcher $launcher)
    {
        $this->launcher = $launcher;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(SubjectMetadata $subjectMetadata, Iteration $iteration, Config $config)
    {
        $tokens = [
            'class' => $subjectMetadata->getBenchmark()->getClass(),
            'file' => $subjectMetadata->getBenchmark()->getPath(),
            'subject' => $subjectMetadata->getName(),
            'revolutions' => $iteration->getVariant()->getRevolutions(),
            'beforeMethods' => var_export($subjectMetadata->getBeforeMethods(), true),
            'afterMethods' => var_export($subjectMetadata->getAfterMethods(), true),
            'parameters' => var_export($iteration->getVariant()->getParameterSet()->getArrayCopy(), true),
            'warmup' => $iteration->getVariant()->getWarmup() ?: 0,
        ];
        $payload = $this->launcher->payload(__DIR__ . '/template/microtime.template', $tokens);

        return $this->launch($payload, $iteration, $config);
    }

    public function healthCheck()
    {
    }

    /**
     * Launch the payload. This method has to return the ResultCollection.
     */
    abstract protected function launch(Payload $payload, Iteration $iteration, Config $config);

    /**
     * {@inheritdoc}
     */
    public function executeMethods(BenchmarkMetadata $benchmark, array $methods)
    {
        $tokens = [
            'class' => $benchmark->getClass(),
            'file' => $benchmark->getPath(),
            'methods' => var_export($methods, true),
        ];

        $payload = $this->launcher->payload(__DIR__ . '/template/execute_static_methods.template', $tokens);
        $payload->launch();
    }
}
