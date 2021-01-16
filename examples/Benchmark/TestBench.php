<?php

namespace PhpBench\Examples\Benchmark;

class TestBench
{
    /**
     * @Revs(10)
     * @Iterations(30)
     * @Assert("mode(variant.time.net) <= mode(baseline.time.net) +/- 10%")
     */
    public function benchFoobar(): void
    {
        md5('Hello PHPNW');
    }
}
