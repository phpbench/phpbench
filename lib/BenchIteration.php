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

class BenchIteration
{
    protected $index;
    protected $parameters;
    protected $time;

    public function __construct($index, $parameters)
    {
        $this->index = $index;
        $this->parameters = $parameters;
    }

    public function getParameter($name)
    {
        if (!isset($this->parameters[$name])) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Unknown iteration parameters "%s"', $name
            ));
        }

        return $this->parameters[$name];
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    public function getIndex()
    {
        return $this->index;
    }

    public function getTime()
    {
        return $this->time;
    }

    public function setTime($time)
    {
        $this->time = $time;
    }
}
