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

class DotsLogger implements LoggerInterface
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

    public function benchmarkStart(BenchmarkMetadata $benchmark)
    {
        static $first = true;

        if ($this->showBench) {
            // do not output a line break on the first run
            if (false === $first) {
                $this->output->writeln('');
            }
            $first = false;

            $this->output->writeln($benchmark->getClass());
        }
    }

    public function benchmarkEnd(BenchmarkMetadata $benchmark)
    {
    }

    public function subjectStart(SubjectMetadata $subject)
    {
    }

    public function subjectEnd(SubjectMetadata $subject)
    {
        $this->output->write('.');
    }
}
