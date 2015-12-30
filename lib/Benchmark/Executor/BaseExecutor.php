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
use PhpBench\Benchmark\Iteration;
use PhpBench\Benchmark\IterationResult;
use PhpBench\Benchmark\Metadata\BenchmarkMetadata;
use PhpBench\Benchmark\Remote\Launcher;
use PhpBench\Benchmark\Remote\Payload;

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
    public function execute(Iteration $iteration, array $options = array())
    {
        $subject = $iteration->getSubject();
        $tokens = array(
            'class' => $subject->getBenchmarkMetadata()->getClass(),
            'file' => $subject->getBenchmarkMetadata()->getPath(),
            'subject' => $subject->getName(),
            'revolutions' => $iteration->getRevolutions(),
            'beforeMethods' => var_export($subject->getBeforeMethods(), true),
            'afterMethods' => var_export($subject->getAfterMethods(), true),
            'parameters' => var_export($iteration->getParameters()->getArrayCopy(), true),
        );
        $payload = $this->launcher->payload(__DIR__ . '/template/microtime.template', $tokens);

        return $this->launch($payload, $iteration, $options);
    }

    /**
     * Launch the payload. This method has to return the IterationResult.
     *
     * @return IterationResult
     */
    abstract protected function launch(Payload $payload, Iteration $iteration, array $options  = array());

    /**
     * {@inheritdoc}
     */
    public function executeMethods(BenchmarkMetadata $benchmark, array $methods)
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
    }
}

