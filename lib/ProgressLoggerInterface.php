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

use PhpBench\Benchmark\Subject;
use Symfony\Component\Console\Output\OutputInterface;

interface ProgressLoggerInterface
{
    public function benchmarkEnd(BenchmarkInterface $case);

    public function benchmarkStart(BenchmarkInterface $case);

    public function subjectEnd(Subject $case);

    public function subjectStart(Subject $case);

    public function setOutput(OutputInterface $output);
}
