<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Progress\Logger;

use PhpBench\Benchmark\Metadata\BenchmarkMetadata;
use PhpBench\Benchmark\Metadata\SubjectMetadata;
use PhpBench\Progress\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;

class NullLogger implements LoggerInterface
{
    public function setOutput(OutputInterface $output)
    {
    }

    public function benchmarkStart(BenchmarkMetadata $benchmark)
    {
    }

    public function benchmarkEnd(BenchmarkMetadata $benchmark)
    {
    }

    public function subjectStart(SubjectMetadata $subject)
    {
    }

    public function subjectEnd(SubjectMetadata $subject)
    {
    }
}
