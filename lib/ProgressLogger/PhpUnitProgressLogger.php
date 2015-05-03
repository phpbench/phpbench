<?php

namespace PhpBench\ProgressLogger;

use PhpBench\BenchProgressLogger;
use PhpBench\BenchCase;
use PhpBench\BenchSubject;
use Symfony\Component\Console\Output\OutputInterface;

class PhpUnitProgressLogger implements BenchProgressLogger
{
    private $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function caseStart(BenchCase $case)
    {
        $this->output->writeln(get_class($case));
    }

    public function caseEnd(BenchCase $case)
    {
        $this->output->writeln('');
    }

    public function subjectStart(BenchSubject $subject)
    {
    }

    public function subjectEnd(BenchSubject $subject)
    {
        $this->output->write('.');
    }

}
