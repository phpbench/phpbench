<?php

namespace PhpBench;

interface BenchReportGenerator
{
    public function generate(BenchCaseCollectionResult $collection);
}
