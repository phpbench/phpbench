<?php

namespace PhpBench;

class ProgressLoggerRegistry
{
    private $progressLoggers;

    public function addProgressLogger($name, ProgressLogger $progressLogger)
    {
        $this->progressLoggers[$name] = $progressLogger;
    }

    public function getProgressLogger($name)
    {
        if (!isset($this->progressLoggers[$name])) {
            throw new \InvalidArgumentException(sprintf(
                'No progress logger with name "%s" has been registered',
                $name
            ));
        }

        return $this->progressLoggers[$name];
    }
}
