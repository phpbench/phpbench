<?php

namespace PhpBench\Examples\Annotations;

use Generator;


// section: all
/**
// endsection: all
// section: beforeClassMethods
 * @BeforeClassMethods("setUpBeforeClass")
// endsection: beforeClassMethods
// section: afterClassMethods
 * @AfterClassMethods("tearDownAfterClass")
// endsection: afterClassMethods
// section: all
 */
class AnnotatedBench
{
// endsection: all
// section: benchTime
    /**
// endsection: benchTime
// section: beforeMethods
     * @BeforeMethods("setUp")
// endsection: beforeMethods
// section: afterMethods
     * @AfterMethods("tearDown")
// endsection: afterMethods
// section: groups
     * @Groups({"one", "two"})
// endsection: groups
// section: iterations
     * @Iterations(10)
// endsection: iterations
// section: revs
     * @Revs(10)
// endsection: revs
// section: sleep
     * @Sleep(1000)
// endsection: sleep
// section: outputTimeUnit
     * @OutputTimeUnit("milliseconds")
// endsection: outputTimeUnit
// section: outputMode
     * @OutputTimeUnit("seconds")
     * @OutputMode("throughput")
// endsection: outputMode
// section: warmup
     * @Warmup(2)
// endsection: warmup
// section: assert
     * @Assert("mode(variant.time.avg) < 200 ms")
// endsection: assert
// section: format
     * @Format("mode(variant.time.avg) as ms ~ 'Hello World'")
// endsection: format
// section: executor
     * @Executor("local")
// endsection: executor
// section: timeout
     * @Timeout(1.0)
// endsection: timeout
// section: retrythreshold
     * @RetryThreshold(20.0)
// endsection: retrythreshold
// section: benchTime
     */
    public function benchTimeItself(): void
    {
        usleep(50);
    }

// endsection: benchTime
// section: skip
    /**
     * @Skip
     */
    public function benchThisWillBeSkipped()
    {
    }

// endsection: skip
// section: paramProviders
    /**
     * @ParamProviders("provideMd5")
     */
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
    /**
     * @ParamProviders({"provideStringsAsArray"})
     */
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
    /**
     * @ParamProviders({"provideStrings", "provideNumbers"})
     */
    public function benchHash($params)
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
