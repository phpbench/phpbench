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

use PhpBench\Benchmark\Benchmark;
use PhpBench\Benchmark\Subject;
use PhpBench\Progress\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;

class VerboseLogger implements LoggerInterface
{
    private $output;

    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function benchmarkStart(Benchmark $benchmark)
    {
        $this->output->writeln(sprintf('<comment>%s</comment>', $benchmark->getClassFqn()));
    }

    public function benchmarkEnd(Benchmark $benchmark)
    {
    }

    public function subjectStart(Subject $subject)
    {
        $this->output->write('  <info>>> </info>' . $subject->getMethodName());
    }

    public function subjectEnd(Subject $subject)
    {
        $this->output->writeln(' [<info>OK</info>]');
    }
}
