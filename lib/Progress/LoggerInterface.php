<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Progress;

use PhpBench\Benchmark\Benchmark;
use PhpBench\Benchmark\Subject;
use Symfony\Component\Console\Output\OutputInterface;

interface LoggerInterface
{
    public function benchmarkEnd(Benchmark $benchmark);

    public function benchmarkStart(Benchmark $benchmark);

    public function subjectEnd(Subject $case);

    public function subjectStart(Subject $case);

    public function setOutput(OutputInterface $output);
}
