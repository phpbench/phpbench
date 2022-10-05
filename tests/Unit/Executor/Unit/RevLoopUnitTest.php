<?php

namespace PhpBench\Tests\Unit\Executor\Unit;

use PhpBench\Executor\Unit\CallSubjectUnit;
use PhpBench\Executor\Unit\RevLoopUnit;

class RevLoopUnitTest extends UnitTestCase
{
    public function testEmptyRevLoop(): void
    {
        $this->executeProgram([
            new RevLoopUnit(),
        ], $this->context()->build(), [
            'rev_loop'
        ]);
        self::assertCount(0, $this->registeredCalls());
    }

    public function testSingleRevLoop(): void
    {
        $this->executeProgram([
            new RevLoopUnit(),
            new CallSubjectUnit(),
        ], $this->context()->withRevolutions(1)->build(), [
            'rev_loop',
            [
                'call_subject',
            ]
        ]);
        self::assertCount(1, $this->registeredCalls());
    }

    public function testMultipleRevLoop(): void
    {
        $this->executeProgram([
            new RevLoopUnit(),
            new CallSubjectUnit(),
        ], $this->context()->withRevolutions(5)->build(), [
            'rev_loop',
            [
                'call_subject',
            ]
        ]);
        self::assertCount(5, $this->registeredCalls());
    }
}
