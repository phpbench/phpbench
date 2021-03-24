<?php

namespace PhpBench\Tests\Unit\Expression\Ast;

use Generator;
use PhpBench\Expression\Ast\BooleanNode;
use PhpBench\Expression\Ast\FloatNode;
use PhpBench\Expression\Ast\IntegerNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\NullNode;
use PhpBench\Expression\Ast\PhpValueFactory;
use PhpBench\Expression\Ast\StringNode;
use PHPUnit\Framework\TestCase;

class PhpValueFactoryTest extends TestCase
{
    /**
     * @dataProvider provideFrom
     *
     * @param mixed $value
     */
    public function testFrom($value, Node $expected): void
    {
        self::assertEquals($expected, PhpValueFactory::fromValue($value));
    }
        
    /**
     * @return Generator<mixed>
     */
    public function provideFrom(): Generator
    {
        yield [1, new IntegerNode(1)];

        yield [1.1, new FloatNode(1.1)];

        yield ['one', new StringNode('one')];

        yield [false, new BooleanNode(false)];

        yield [null, new NullNode()];
    }
}
