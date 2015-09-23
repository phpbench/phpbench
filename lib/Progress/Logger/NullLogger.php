<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Progress\Logger;

use PhpBench\Benchmark\Benchmark;
use PhpBench\Benchmark\Subject;
use PhpBench\Progress\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;

class NullLogger implements LoggerInterface
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
