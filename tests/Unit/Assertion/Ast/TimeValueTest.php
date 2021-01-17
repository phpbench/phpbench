<?php

namespace PhpBench\Tests\Unit\Assertion\Ast;

use PhpBench\Assertion\Ast\IntegerNode;
use PhpBench\Assertion\Ast\TimeValue;
use PHPUnit\Framework\TestCase;

class TimeValueTest extends TestCase
{
    public function testReturnsUnitForAsUnitByDefault(): void
    {
        $value = new TimeValue(new IntegerNode(10), 'seconds', null);
        self::assertEquals('seconds', $value->asUnit());
    }
    public function testAsUnit(): void
    {
        $value = new TimeValue(new IntegerNode(10), 'seconds', 'milliseconds');
        self::assertEquals('milliseconds', $value->asUnit());
    }
}
