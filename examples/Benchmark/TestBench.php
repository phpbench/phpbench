<?php

namespace PhpBench\Examples\Benchmark;

class TestBench
{
    /**
     * @Revs(10)
     * @Iterations(30)
     * @Assert("variant.mode <= baseline.mode +/- 10%")
     */
    public function benchFoobar(): void
    {
        md5('Hello PHPNW');
    }
}
