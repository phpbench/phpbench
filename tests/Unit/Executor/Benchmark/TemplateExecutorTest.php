<?php

namespace PhpBench\Tests\Unit\Executor\Benchmark;

use PhpBench\Executor\Benchmark\TemplateExecutor;
use PhpBench\Executor\ExecutionContext;
use PhpBench\Model\ParameterSet;
use PhpBench\Model\Result\TimeResult;
use PhpBench\Registry\Config;
use PhpBench\Remote\Launcher as PhpBenchLauncher;
use PHPUnit\Framework\TestCase;

class TemplateExecutorTest extends TestCase
{
    public function testUnsafeParameters(): void
    {
        $result = (new TemplateExecutor(
            new PhpBenchLauncher(),
            __DIR__ . '/template/unsafe_parameters.template'
        ))->execute(new ExecutionContext(
            'foo',
            '/bar',
            'baz',
            1, [], [], ParameterSet::fromUnwrappedParameters('foo', [
                'time' => 100
            ])
        ), new Config('test', [
            TemplateExecutor::OPTION_SAFE_PARAMETERS => false,
        ]));
        $first = $result->byType(TimeResult::class)->first();
        self::assertEquals(100, $first->getMetrics()['net']);
    }
}
