<?php

namespace PhpBench\Tests\Unit\Executor\Unit;

use PHPUnit\Framework\TestCase;
use PhpBench\Executor\ExecutionContext;
use PhpBench\Executor\Unit\WarmupUnit;
use PhpBench\Executor\Unit\RootUnit;

class WarmupUnitTest extends UnitTestCase
{
    public function testZeroWarmups(): void
    {
        $this->executeProgram([
            'warmup' => new WarmupUnit(),
        ], $this->context()->build(), [
            'warmup'
        ]);
        self::assertCount(0, $this->registeredCalls());
    }

    public function testWarmups(): void
    {
        $this->executeProgram([
            'warmup' => new WarmupUnit(),
        ], $this->context()->withWarmup(2)->build(), [
            'warmup'
        ]);
        self::assertCount(2, $this->registeredCalls());
    }

    public function testWarmupWithParams(): void
    {
        $this->executeProgram([
            'warmup' => new WarmupUnit(),
        ], $this->context()->withWarmup(1)->withParameters([
            'one' => 'two',
        ])->build(), [
            'warmup'
        ]);
        $calls = $this->registeredCalls();
        self::assertCount(1, $calls);
        self::assertEquals(['one' => 'two'], $calls[0]);
    }
}
