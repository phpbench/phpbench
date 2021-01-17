<?php

namespace PhpBench\Tests\Unit\Assertion;

use PHPUnit\Framework\TestCase;
use PhpBench\Assertion\Exception\ExpressionEvaluatorError;
use PhpBench\Assertion\ExpressionFunctions;

class ExpressionFunctionsTest extends TestCase
{
    public function testErrorOnUnknownFunction(): void
    {
        $this->expectException(ExpressionEvaluatorError::class);
        $this->expectExceptionMessage('Unknown function');
        (new ExpressionFunctions(['one' => function () {}]))->execute('foo', []);
    }
}
