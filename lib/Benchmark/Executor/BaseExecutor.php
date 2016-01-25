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

use PhpBench\Benchmark\ExecutorInterface;
use PhpBench\Benchmark\Remote\Launcher;
use PhpBench\Benchmark\Remote\Payload;
use PhpBench\Model\Benchmark;
use PhpBench\Model\Iteration;
use PhpBench\Model\IterationResult;
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
    public function execute(Iteration $iteration, Config $config)
    {
        $subject = $iteration->getSubject();
        $tokens = array(
            'class' => $subject->getBenchmark()->getClass(),
            'file' => $subject->getBenchmark()->getPath(),
            'subject' => $subject->getName(),
            'revolutions' => $iteration->getRevolutions(),
            'beforeMethods' => var_export($subject->getBeforeMethods(), true),
            'afterMethods' => var_export($subject->getAfterMethods(), true),
            'parameters' => var_export($iteration->getParameters()->getArrayCopy(), true),
            'warmup' => $iteration->getWarmup() ?: 0,
        );
        $payload = $this->launcher->payload(__DIR__ . '/template/microtime.template', $tokens);

        return $this->launch($payload, $iteration, $config);
    }

    /**
     * Launch the payload. This method has to return the IterationResult.
     *
     * @return IterationResult
     */
    abstract protected function launch(Payload $payload, Iteration $iteration, Config $config);

    /**
     * {@inheritdoc}
     */
    public function executeMethods(Benchmark $benchmark, array $methods)
    {
        $tokens = array(
            'class' => $benchmark->getClass(),
            'file' => $benchmark->getPath(),
            'methods' => var_export($methods, true),
        );

        $payload = $this->launcher->payload(__DIR__ . '/template/benchmark_static_methods.template', $tokens);
        $payload->launch();
    }

    /**
     * {@inheritdoc}
     */
    public function getSchema()
    {
        return array();
    }
}
