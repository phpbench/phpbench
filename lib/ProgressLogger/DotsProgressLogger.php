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

use PhpBench\BenchmarkInterface;
use PhpBench\Benchmark\Subject;
use Symfony\Component\Console\Output\OutputInterface;
use PhpBench\ProgressLoggerInterface;

class DotsProgressLogger implements ProgressLoggerInterface
{
    private $output;
    private $showBench;

    public function __construct($showBench = false)
    {
        $this->showBench = $showBench;
    }

    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function benchmarkStart(BenchmarkInterface $case)
    {
        static $first = true;

        if ($this->showBench) {
            // do not output a line break on the first run
            if (false === $first) {
                $this->output->writeln('');
            }
            $first = false;

            $this->output->writeln(get_class($case));
        }
    }

    public function benchmarkEnd(BenchmarkInterface $case)
    {
    }

    public function subjectStart(Subject $subject)
    {
    }

    public function subjectEnd(Subject $subject)
    {
        $this->output->write('.');
    }
}
