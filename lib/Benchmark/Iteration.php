<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Benchmark;

use PhpBench\Benchmark\Metadata\SubjectMetadata;

/**
 * Represents the data required to execute a single iteration.
 */
class Iteration
{
    private $subject;
    private $revolutions;
    private $parameters = array();
    private $index;

    public function __construct(
        $index,
        SubjectMetadata $subject,
        $revolutions,
        array $parameters
    ) {
        $this->index = $index;
        $this->subject = $subject;
        $this->revolutions = $revolutions;
        $this->parameters = $parameters;
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function getRevolutions()
    {
        return $this->revolutions;
    }

    public function getParameters()
    {
        return $this->parameters;
    }
}
