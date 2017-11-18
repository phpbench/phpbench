<?php

namespace PhpBench\Extensions\Elastic\Encoder;

use PhpBench\Model\Suite;
use PhpBench\Environment\Information;
use PhpBench\Model\Benchmark;
use PhpBench\Model\Subject;
use PhpBench\Model\Variant;
use PhpBench\Model\Iteration;
use DateTime;

class DocumentDecoder
{
    public function decode(array $documents): Suite
    {
        return (new DocumentReconstructor($documents))->getSuite();
    }
}
