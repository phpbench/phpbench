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
    private $name;
    private $description;
    private $iterationsResults;

    public function __construct($name, $description, array $iterationsResults)
    {
        $this->iterationsResults = $iterationsResults;
        $this->name = $name;
        $this->description = $description;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getIterationsResults()
    {
        return $this->iterationsResults;
    }
}
