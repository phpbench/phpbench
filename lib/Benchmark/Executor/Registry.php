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

    public function getProfiler($name)
    {
        $executor = $this->getExecutor($name);

        if (!$executor instanceof ProfilerInterface) {
            $profilers = array_filter($this->executors, function ($executor) {
                if ($executor instanceof ProfilerInterface) {
                    return true;
                }

                return false;
            });

            throw new \InvalidArgumentException(sprintf(
                'Executor "%s" is not a profiler, registered profilers: "%s"',
                $name, implode('", "', array_keys($profilers))
            ));
        }

        return $executor;
    }
}
