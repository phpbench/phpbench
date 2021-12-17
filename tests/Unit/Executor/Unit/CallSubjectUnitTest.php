<?php

namespace PhpBench\Tests\Unit\Executor\Unit;

use PHPUnit\Framework\TestCase;
use PhpBench\Executor\ExecutionContext;
use PhpBench\Executor\Unit\CallSubjectUnit;
use PhpBench\Executor\Unit\RootUnit;

class CallSubjectUnitTest extends UnitTestCase
{
    public function testCallsSubject(): void
    {
        $this->executeProgram([
            'call_subject' => new CallSubjectUnit(),
        ], $this->context()->build(), [
            'call_subject'
        ]);
        self::assertCount(1, $this->registeredCalls());
    }

    public function testCallsSubjectWithParams(): void
    {
        $this->executeProgram([
            'call_subject' => new CallSubjectUnit(),
        ], $this->context()->withParameters([
            'one' => 'two',
        ])->build(), [
            'call_subject'
        ]);
        $calls = $this->registeredCalls();
        self::assertCount(1, $calls);
        self::assertEquals([
            'one' => 'two',
        ], $calls[0]);
    }
}
