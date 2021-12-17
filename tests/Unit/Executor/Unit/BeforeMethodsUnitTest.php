<?php

namespace PhpBench\Tests\Unit\Executor\Unit;

use PHPUnit\Framework\TestCase;
use PhpBench\Executor\ExecutionContext;
use PhpBench\Executor\Unit\BeforeMethodsUnit;
use PhpBench\Executor\Unit\CallSubjectUnit;
use PhpBench\Executor\Unit\HrtimeSampler;
use PhpBench\Executor\Unit\RootUnit;

class BeforeMethodsUnitTest extends UnitTestCase
{
    public function testSample(): void
    {
        $result = $this->executeProgram([
            new BeforeMethodsUnit(),
            new CallSubjectUnit(),
        ], $this->context()->withBeforeMethods(['register', 'register'])->build(), [
            'before_methods',
            'call_subject',
        ]);
        self::assertCount(3, $this->registeredCalls());
    }
}
