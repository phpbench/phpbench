<?php

namespace PhpBench\Tests\Unit\Expression\Func;

use PhpBench\Expression\Ast\DisplayAsTimeNode;
use PhpBench\Expression\Ast\IntegerNode;
use PhpBench\Expression\Ast\StringNode;
use PhpBench\Expression\Ast\UnitNode;
use PhpBench\Expression\Exception\EvaluationError;
use PhpBench\Expression\Func\DisplayAsTimeFunction;
use PhpBench\Tests\Unit\Expression\FunctionTestCase;

class DisplayAsTimeFunctionTest extends FunctionTestCase
{
    public function testDisplayTime(): void
    {
        self::assertEquals(new DisplayAsTimeNode(
            new IntegerNode(7),
            new UnitNode(new StringNode("milliseconds")),
            new IntegerNode(3),
            new StringNode("throughput")
        ), $this->eval(
            new DisplayAsTimeFunction(),
            '7, "milliseconds", 3, "throughput"'
        ));
    }

    public function testDisplayTwoArgs(): void
    {
        self::assertEquals(new DisplayAsTimeNode(
            new IntegerNode(7),
            new UnitNode(new StringNode("milliseconds"))
        ), $this->eval(
            new DisplayAsTimeFunction(),
            '7, "milliseconds"'
        ));
    }

    public function testDisplayWithNullUnit(): void
    {
        self::assertEquals(new DisplayAsTimeNode(
            new IntegerNode(7),
            new UnitNode(new StringNode("microseconds"))
        ), $this->eval(
            new DisplayAsTimeFunction(),
            '7, null'
        ));
    }

    public function testDisplayWithInvalidType(): void
    {
        $this->expectException(EvaluationError::class);
        $this->eval(
            new DisplayAsTimeFunction(),
            '7, 10'
        );
    }
}
