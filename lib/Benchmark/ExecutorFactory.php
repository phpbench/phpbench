<?php

namespace PhpBench\Benchmark;

use PhpBench\DependencyInjection\Container;
use PhpBench\Benchmark\ExecutorInterface;

class ExecutorFactory
{
    private $executors = array();

    public function addExecutor($name, ExecutorInterface $executor)
    {
        $this->executors[$name] = $executor;
    }

    public function getExecutor($name)
    {
        if (!isset($this->executors[$name])) {
            throw new \InvalidArgumentException(sprintf(
                'Unknown executor "%s", known executors: "%s"',
                $name, implode('", "', array_keys($this->executors))
            ));
        }

        return $this->executors[$name];
    }
}
