<?php

namespace PhpBench\Console;

use Symfony\Component\Console\Output\OutputInterface;

interface OutputAware
{
    public function setOutput(OutputInterface $output);
}
