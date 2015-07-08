<?php

namespace PhpBench\Report;

use PhpBench\Result\SuiteResult;

interface DataProvider
{
    public function provide(SuiteResult $suiteResult);
}
