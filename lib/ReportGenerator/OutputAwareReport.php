<?php

namespace PhpBench\ReportGenerator;

interface OutputAwareReport
{
    public function setOutput(OutputInterface $output);
}
