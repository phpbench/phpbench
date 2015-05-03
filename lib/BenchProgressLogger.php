<?php

namespace PhpBench;

interface BenchProgressLogger
{
    public function caseComplete($case);

    public function subjectComplete($case);
}
