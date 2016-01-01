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
