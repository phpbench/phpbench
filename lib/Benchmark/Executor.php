<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Benchmark;

use Symfony\Component\Process\Process;
use PhpBench\BenchmarkInterface;
use PhpBench\Benchmark\Telespector;
use PhpBench\Benchmark\Subject;

/**
 * This class generates a benchmarking script and places it in the systems
 * temp. directory and then executes it. The generated script then returns the
 * time taken to execute the benchmark and the memory consumed.
 */
class Executor
{
    /**
     * @var string
     */
    private $bootstrap;

    /**
     * @var string
     */
    private $configDir;

    /**
     * @var Telespector
     */
    private $telespector;

    /**
     * @param Telespector $telespector
     * @param string $configPath
     * @param string $bootstrap
     */
    public function __construct(Telespector $telespector, $configPath, $bootstrap)
    {
        $this->configDir = dirname($configPath);
        $this->bootstrap = $bootstrap;
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
            'bootstrap' => $this->getBootstrapPath(),
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

    private function getBootstrapPath()
    {
        if (!$this->bootstrap) {
            return;
        }

        // if the path is absolute, return it unmodified
        if ('/' === substr($this->bootstrap, 0, 1)) {
            return $this->bootstrap;
        }

        return $this->configDir . '/' . $this->bootstrap;
    }
}
