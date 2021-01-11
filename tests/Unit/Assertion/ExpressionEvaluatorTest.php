<?php

namespace PhpBench\Tests\Unit\Assertion;

use PhpBench\Assertion\Ast\ThroughputValue;
use PhpBench\Assertion\ExpressionEvaluator;
use PhpBench\Assertion\MessageFormatter\NodeMessageFormatter;
use PHPUnit\Framework\TestCase;

class ExpressionEvaluatorTest extends TestCase
{
    public function testThroughput(): void
    {
        $eval = new ExpressionEvaluator(new NodeMessageFormatter([]), []);
        self::assertEquals(1000000, $eval->evaluate(new ThroughputValue(1.0, 'second')));
    }
}
