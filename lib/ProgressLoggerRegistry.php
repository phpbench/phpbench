<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench;

class ProgressLoggerRegistry
{
    private $progressLoggers;

    public function addProgressLogger($name, ProgressLoggerInterface $progressLogger)
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
