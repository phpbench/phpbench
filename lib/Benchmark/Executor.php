<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Benchmark;

/**
 * This class generates a benchmarking script and places it in the systems
 * temp. directory and then executes it. The generated script then returns the
 * time taken to execute the benchmark and the memory consumed.
 */
class Executor
{
    /**
     * @var Telespector
     */
    private $telespector;

    /**
     * @param Telespector $telespector
     * @param string $configPath
     * @param string $bootstrap
     */
    public function __construct(Telespector $telespector)
    {
        $this->telespector = $telespector;
    }

    /**
     * @param Subject $subject
     * @param int $revolutions
     * @param array $parameters
     */
    public function execute(Subject $subject, $revolutions = 0, array $parameters = array())
    {
        $tokens = array(
            'class' => $subject->getBenchmark()->getClassFqn(),
            'file' => $subject->getBenchmark()->getPath(),
            'subject' => $subject->getMethodName(),
            'revolutions' => $revolutions,
            'beforeMethods' => var_export($subject->getBeforeMethods(), true),
            'afterMethods' => var_export($subject->getAfterMethods(), true),
            'parameters' => var_export($parameters, true),
        );

        $result = $this->telespector->execute(__DIR__ . '/template/runner.template', $tokens);

        return $result;
    }
}
