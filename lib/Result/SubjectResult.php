<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Result;

class SubjectResult
{
    private $identifier;
    private $name;
    private $groups;
    private $iterationsResults;
    private $parameters;

    public function __construct($identifier, $name, array $groups, array $parameters, array $iterationsResults)
    {
        $this->identifier = $identifier;
        $this->iterationsResults = $iterationsResults;
        $this->name = $name;
        $this->groups = $groups;
        $this->parameters = $parameters;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getIterationsResults()
    {
        return $this->iterationsResults;
    }

    public function getGroups()
    {
        return $this->groups;
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }
}
