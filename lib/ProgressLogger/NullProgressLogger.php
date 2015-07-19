<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\ProgressLogger;

use PhpBench\Benchmark\Subject;
use PhpBench\Benchmark;
use PhpBench\ProgressLogger;
use Symfony\Component\Console\Output\OutputInterface;

class NullProgressLogger implements ProgressLogger
{
    public function setOutput(OutputInterface $output)
    {
    }

    public function benchmarkStart(Benchmark $benchmark)
    {
    }

    public function benchmarkEnd(Benchmark $benchmark)
    {
    }

    public function subjectStart(Subject $subject)
    {
    }

    public function subjectEnd(Subject $subject)
    {
    }
}
