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

use PhpBench\Benchmark\Metadata\SubjectMetadata;
use PhpBench\Benchmark\Remote\Launcher;
use PhpBench\Benchmark\ExecutorInterface;

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
     * {@inheritDoc}
     */
    public function execute(SubjectMetadata $subject, $revolutions = 1, array $parameters = array(), $options = array())
    {
        $tokens = array(
            'class' => $subject->getBenchmarkMetadata()->getClass(),
            'file' => $subject->getBenchmarkMetadata()->getPath(),
            'subject' => $subject->getName(),
            'revolutions' => $revolutions,
            'beforeMethods' => var_export($subject->getBeforeMethods(), true),
            'afterMethods' => var_export($subject->getAfterMethods(), true),
            'parameters' => var_export($parameters, true),
        );

        $result = $this->launcher->launch(__DIR__ . '/template/microtime.template', $tokens);
        $result['calls'] = null;

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function getSchema()
    {
        return array();
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultConfig()
    {
        return array();
    }
}
