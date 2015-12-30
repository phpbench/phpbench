<?php

namespace PhpBench\Benchmark\Executor;

use PhpBench\DependencyInjection\Container;
use PhpBench\Benchmark\ExecutorInterface;
use PhpBench\Benchmark\ProfilerInterface;

class Registry
{
    private $executors = array();

    public function register($name, ExecutorInterface $executor)
    {
        $this->executors[$name] = $executor;
    }

    public function getExecutor($name)
    {
        if (!isset($this->executors[$name])) {
            throw new \InvalidArgumentException(sprintf(
                'Unknown executor: "%s", known executors: "%s"',
                $name,
                implode('", "', array_keys($this->executors))
            ));
        }

        return $this->executors[$name];
    }
}
