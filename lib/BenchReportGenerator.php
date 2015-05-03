<?php

namespace PhpBench;

interface BenchReportGenerator
{
    public function generate(BenchCaseCollection $collection);
}
