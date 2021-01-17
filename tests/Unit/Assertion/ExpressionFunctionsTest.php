<?php

namespace PhpBench\Tests\Unit\Assertion;

use PhpBench\Assertion\Exception\ExpressionEvaluatorError;
use PhpBench\Assertion\ExpressionFunctions;
use PHPUnit\Framework\TestCase;

class ExpressionFunctionsTest extends TestCase
{
    public function testErrorOnUnknownFunction(): void
    {
        $this->expectException(ExpressionEvaluatorError::class);
        $this->expectExceptionMessage('Unknown function');
        (new ExpressionFunctions(['one' => function (): void {
        }]))->execute('foo', []);
    }
}
