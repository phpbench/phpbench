<?php

namespace PhpBench\Examples\Attributes;

use Attribute;
use PhpBench\Benchmark\Metadata\Attributes\AfterMethods;
use PhpBench\Benchmark\Metadata\Attributes\Assert;
use PhpBench\Benchmark\Metadata\Attributes\BeforeMethods;
use PhpBench\Benchmark\Metadata\Attributes\Executor;
use PhpBench\Benchmark\Metadata\Attributes\Groups;
use PhpBench\Benchmark\Metadata\Attributes\Iterations;
use PhpBench\Benchmark\Metadata\Attributes\OutputMode;
use PhpBench\Benchmark\Metadata\Attributes\OutputTimeUnit;
use PhpBench\Benchmark\Metadata\Attributes\ParamProviders;
use PhpBench\Benchmark\Metadata\Attributes\Revs;
use PhpBench\Benchmark\Metadata\Attributes\Skip;
use PhpBench\Benchmark\Metadata\Attributes\Sleep;
use PhpBench\Benchmark\Metadata\Attributes\Subject;
use PhpBench\Benchmark\Metadata\Attributes\Timeout;
use PhpBench\Benchmark\Metadata\Attributes\Warmup;

class AttriutedBench
{
    #[ForeignAttribute]
    #[BeforeMethods("setUp")]
    #[AfterMethods("tearDown")]
    #[Groups(['one', 'two'])]
    #[Iterations(10)]
    #[ParamProviders('provideParams')]
    #[Revs(10)]
    #[Sleep(1)]
    #[OutputTimeUnit('milliseconds')]
    #[OutputMode('throughput')]
    #[Warmup(2)]
    #[Assert('12 < 13')]
    #[Executor('local', [])]
    #[Timeout(1E6)]
    public function benchFoo(): void
    {
    }

    #[Skip]
    public function benchSkipped(): void
    {
    }

    public function provideParams(): array
    {
        return [[]];
    }

    public function setUp(): void
    {
    }
    public function tearDown(): void
    {
    }

}

#[Attribute]
class ForeignAttribute
{
}
