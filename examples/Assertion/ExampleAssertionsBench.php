<?php

namespace PhpBench\Examples\Assertion;

// section: all
class ExampleAssertionsBench
{
// endsection: all
    // section: bench_time
    /**
     * @Assert("mode(variant.time.avg) < 200 microseconds +/- 10%")
     */
    public function benchTime(): void
    {
        usleep(100);
    }
    // endsection: bench_time

    // section: bench_time_baseline
    /**
     * @Assert("mode(variant.time.avg) < mode(baseline.time.avg) +/- 10%")
     */
    public function benchTimeBaseline(): void
    {
        usleep(100);
    }
    // endsection: bench_time_baseline

    // section: data_access
    /**
     * @Assert("mode(variant.time.avg) < 10")
     * @Assert("mode(variant.time.net) < 10")
     * @Assert("mode(variant.mem.peak) < 10")
     * @Assert("mode(variant.mem.final) < 10")
     * @Assert("mode(variant.mem.real) < 10")
     *
     * @Assert("mode(baseline.time.net) < 10")
     * @Assert("mode(baseline.time.avg) < 10")
     * @Assert("mode(baseline.mem.peak) < 10")
     * @Assert("mode(baseline.mem.final) < 10")
     * @Assert("mode(baseline.mem.real) < 10")
     */
    // endsection: data_access
    public function benchDataExamples(): void
    {
        usleep(100);
    }
    // endsection: bench_time
// section: all
}
// endsection: all
