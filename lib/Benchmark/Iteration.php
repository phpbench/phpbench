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

use PhpBench\Exception\InvalidArgumentException;

class Iteration
{
    private $index;
    private $parameters;
    private $revs;

    public function __construct($index, $parameters, $revs)
    {
        $this->index = $index;
        $this->parameters = $parameters;
        $this->revs = $revs;
    }

    public function getParameter($name)
    {
        if (!isset($this->parameters[$name])) {
            throw new InvalidArgumentException(sprintf(
                'Unknown iteration parameters "%s", known parameters: "%s"',
                $name,
                implode('", "', array_keys($this->parameters))
            ));
        }

        return $this->parameters[$name];
    }

    public function setParameter($name, $value)
    {
        $this->parameters[$name] = $value;
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    public function getIndex()
    {
        return $this->index;
    }

    public function getRevs()
    {
        return $this->revs;
    }
}
