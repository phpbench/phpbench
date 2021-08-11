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
            1,
            [],
            [],
            ParameterSet::fromUnserializedValues('foo', [
                'time' => 100
            ])
        ), new Config('test', [
            TemplateExecutor::OPTION_SAFE_PARAMETERS => false,
        ]));
        $first = $result->byType(TimeResult::class)->first();
        self::assertEquals(100, $first->getMetrics()['net']);
    }

    public function testBCOmitUnsafeParameters(): void
    {
        $result = (new TemplateExecutor(
            new PhpBenchLauncher(),
            __DIR__ . '/template/unsafe_parameters.template'
        ))->execute(new ExecutionContext(
            'foo',
            '/bar',
            'baz',
            1,
            [],
            [],
            [
                'time' => 10
            ]
        ), new Config('test', [
        ]));
        $first = $result->byType(TimeResult::class)->first();
        self::assertEquals(10, $first->getMetrics()['net']);
    }
}
