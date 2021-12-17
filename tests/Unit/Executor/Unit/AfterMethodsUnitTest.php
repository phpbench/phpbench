<?php

namespace PhpBench\Tests\Unit\Executor\Unit;

use PhpBench\Executor\Unit\AfterMethodsUnit;
use PhpBench\Executor\Unit\CallSubjectUnit;

class AfterMethodsUnitTest extends UnitTestCase
{
    public function testAfter(): void
    {
        $result = $this->executeProgram([
            new AfterMethodsUnit(),
            new CallSubjectUnit(),
        ], $this->context()->withAfterMethods(['register', 'register'])->build(), [
            'after_methods',
            'call_subject',
        ]);
        self::assertCount(3, $this->registeredCalls());
    }

    public function testAfterWithParams(): void
    {
        $result = $this->executeProgram([
            new AfterMethodsUnit(),
            new CallSubjectUnit(),
        ], $this->context()->withAfterMethods(
            ['register']
        )->withParameters([
            'one' => 'two',
        ])->build(), [
            'after_methods',
            'call_subject',
        ]);
        $calls = $this->registeredCalls();
        self::assertCount(2, $calls);
        self::assertEquals(['one' => 'two'], $calls[1]);
    }
}
