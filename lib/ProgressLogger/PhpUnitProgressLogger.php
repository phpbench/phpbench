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

use PhpBench\Benchmark;
use PhpBench\Benchmark\Subject;
use PhpBench\ProgressLogger;

class PhpUnitProgressLogger implements ProgressLogger
{
    private $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function benchmarkStart(Benchmark $case)
    {
        $this->output->writeln(get_class($case));
    }

    public function benchmarkEnd(Benchmark $case)
    {
        $this->output->writeln('');
    }

    public function subjectStart(Subject $subject)
    {
    }

    public function subjectEnd(Subject $subject)
    {
        $this->output->write('.');
    }
}
