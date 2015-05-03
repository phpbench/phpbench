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

class BenchSubjectResult
{
    private $subject;
    private $iterations;

    public function __construct(BenchSubject $subject, $iterations)
    {
        $this->subject = $subject;
        $this->iterations = $iterations;
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function getIterations()
    {
        return $this->iterations;
    }
}
