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

use PhpBench\Benchmark\Iteration;
use PhpBench\Benchmark\Metadata\BenchmarkMetadata;
use PhpBench\Benchmark\Metadata\SubjectMetadata;
use PhpBench\Progress\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;

class VerboseLogger implements LoggerInterface
{
    private $output;
    private $lastSubject;
    private $lastRetry;

    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function benchmarkStart(BenchmarkMetadata $benchmark)
    {
        $this->output->writeln(sprintf('<comment>%s</comment>', $benchmark->getClass()));
    }

    public function benchmarkEnd(BenchmarkMetadata $benchmark)
    {
    }

    public function subjectStart(SubjectMetadata $subject)
    {
        $this->lastRetry = null;
        $this->lastSubject = $this->lastRetry = sprintf('  <info>>> </info>%s:', $subject->getName());
        $this->output->write($this->lastSubject);
    }

    public function subjectEnd(SubjectMetadata $subject)
    {
        $this->output->writeln(' [<info>OK</info>]');
    }

    public function iterationStart(Iteration $iteration)
    {
        static $count;
        $this->output->write($this->lastIteration = sprintf(
            "\x0D%s I%s #%s ",
            $this->lastRetry,
            $iteration->getIndex(),
            $iteration->getParameters()->getIndex()
        ));
        $count++;
    }

    public function iterationEnd(Iteration $iteration)
    {
    }

    public function retryStart($rejectionCount)
    {
        $this->output->write($this->lastRetry = sprintf(
            "\x0D%s R%d",
            $this->lastSubject,
            $rejectionCount
        ));
    }
}
