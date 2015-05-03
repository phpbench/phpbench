<?php

namespace PhpBench;

use PhpBench\BenchSubject;
use PhpBench\BenchCase;

interface BenchProgressLogger
{
    public function caseEnd(BenchCase $case);

    public function caseStart(BenchCase $case);

    public function subjectEnd(BenchSubject $case);

    public function subjectStart(BenchSubject $case);
}
