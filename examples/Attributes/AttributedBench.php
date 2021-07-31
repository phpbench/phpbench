<?php

namespace PhpBench\Examples\Attributes;

use Generator;
// section: all
use PhpBench\Attributes as Bench;

// endsection: all
// section: beforeClassMethods
#[Bench\BeforeClassMethods(['setUpBeforeClass'])]
// endsection: beforeClassMethods
// section: afterClassMethods
#[Bench\AfterClassMethodsClassMethods(['tearDownBeforeClass'])]
// endsection: afterClassMethods
// section: all
class AttributedBench
{
// endsection: all
// section: benchTime
// endsection: benchTime
// section: beforeMethods
    #[Bench\BeforeMethods('setUp')]
// endsection: beforeMethods
// section: afterMethods
    #[Bench\AfterMethods("tearDown")]
// endsection: afterMethods
// section: groups
    #[Bench\Groups(["one", "two"])]
// endsection: groups
// section: iterations
    #[Bench\Iterations(10)]
// endsection: iterations
// section: revs
    #[Bench\Revs(10)]
// endsection: revs
// section: sleep
    #[Bench\Sleep(1000)]
// endsection: sleep
// section: outputTimeUnit
    #[Bench\OutputTimeUnit('milliseconds')]
// endsection: outputTimeUnit
// section: outputMode
    #[Bench\OutputTimeUnit('seconds')]
    #[Bench\OutputMode('throughput')]
// endsection: outputMode
// section: warmup
    #[Bench\Warmup(2)]
// endsection: warmup
// section: assert
    #[Bench\Assert('mode(variant.time.avg) < 200 ms')]
// endsection: assert
// section: format
    #[Bench\Format('mode(variant.time.avg) ~ " Hello World"')]
// endsection: format
// section: executor
    #[Bench\Executor('local')]
// endsection: executor
// section: timeout
    #[Bench\Timeout(1.0)]
// endsection: timeout
// section: retrythreshold
    #[Bench\RetryThreshold(20.0)]
// endsection: retrythreshold
// section: benchTime
    public function benchTimeItself(): void
    {
        usleep(50);
    }

// endsection: benchTime
// section: skip
     #[Bench\Skip]
    public function benchThisWillBeSkipped()
    {
    }

// endsection: skip
// section: paramProviders
    #[Bench\ParamProviders(['provideMd5'])]
    public function benchMd5(array $params): void
    {
        hash('md5', $params['string']);
    }

    public function provideMd5(): Generator
    {
        yield 'hello' => [ 'string' => 'Hello World!' ];
        yield 'goodbye' => [ 'string' => 'Goodbye Cruel World!' ];
    }

// endsection: paramProviders
// section: beforeMethods
    public function setUp(): void
    {
        // do somrthing before the benchmark
    }

// endsection: beforeMethods

// section: afterMethods
    public function tearDown(): void
    {
        // do somrthing after the benchmark
    }

// endsection: afterMethods
// section: beforeClassMethods
    public static function setUpBeforeClass(): void
    {
        // do somrthing before the benchmark
    }

// endsection: beforeClassMethods
// section: afterClassMethods
    public static function tearDownAfterClass(): void
    {
        // do somrthing after the benchmark
    }

// endsection: afterClassMethods

// section: paramIterable
    #[Bench\ParamProviders('provideStringsAsArray')]
    public function benchIterable(array $params): void
    {
        $helloThenGoodbye = $params['string'];
    }

    public function provideStringsAsArray(): array
    {
        return [
            'hello' => [ 'string' => 'Hello World!' ],
            'goodbye' => [ 'string' => 'Goodbye Cruel World!' ]
        ];
    }

// endsection: paramIterable

// section: paramMultiple
    #[Bench\ParamProviders(['provideStrings', 'provideNumbers'])]
    public function benchHash(array $params)
    {
        hash($params['algorithm'], $params['string']);
    }

    public function provideStrings()
    {
        yield 'hello' => [ 'string' => 'Hello World!' ];
        yield 'goodbye' => [ 'string' => 'Goodbye Cruel World!' ];
    }

    public function provideNumbers()
    {
        yield 'md5' => [ 'algorithm' => 'md5' ];
        yield 'sha1' => [ 'algorithm' => 'sha1' ];
    }
 
// endsection: paramMultiple
// section: all
}
// endsection: all
