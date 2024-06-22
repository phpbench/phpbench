<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace PhpBench\Progress;

use InvalidArgumentException;

class LoggerRegistry
{
    /** @var array<string, LoggerInterface> */
    private array $progressLoggers = [];

    /**
     * @param string $name
     */
    public function addProgressLogger($name, LoggerInterface $progressLogger): void
    {
        $this->progressLoggers[$name] = $progressLogger;
    }

    /**
     * @param string $name
     *
     * @return LoggerInterface
     */
    public function getProgressLogger($name)
    {
        if (!isset($this->progressLoggers[$name])) {
            throw new InvalidArgumentException(sprintf(
                'No progress logger with name "%s" has been registered, known progress loggers: "%s"',
                $name,
                implode('", "', array_keys($this->progressLoggers))
            ));
        }

        return $this->progressLoggers[$name];
    }
}
