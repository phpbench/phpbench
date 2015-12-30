<?php

namespace PhpBench\Extensions\XDebug;

use PhpBench\Benchmark\Iteration;

class XDebugUtil
{
    public static function filenameFromIteration(Iteration $iteration)
    {
        $name = sprintf(
            '%s::%s.P%s.cachegrind',
            $iteration->getSubject()->getBenchmarkMetadata()->getClass(),
            $iteration->getSubject()->getName(),
            $iteration->getParameters()->getIndex()
        );

        $name = str_replace('\\', '_', $name);
        $name = str_replace('/', '_', $name);

        return $name;
    }
}
