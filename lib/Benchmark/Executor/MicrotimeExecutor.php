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
use PhpBench\Benchmark\Remote\Launcher;

/**
 * This class generates a benchmarking script and places it in the systems
 * temp. directory and then executes it. The generated script then returns the
 * time taken to execute the benchmark and the memory consumed.
 */
class MicrotimeExecutor implements ExecutorInterface
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
            'parameters' => var_export($iteration->getParameters(), true),
        );

        $payload = $this->launcher->payload(__DIR__ . '/template/microtime.template', $tokens);
        $payload->launch();

        return new IterationResult($payload);
    }

    /**
     * {@inheritdoc}
     */
    public function getSchema()
    {
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultConfig()
    {
        return array();
    }
}
