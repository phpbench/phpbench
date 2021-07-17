<?php

namespace PhpBench\Tests\Unit\Expression\NodeEvaluator;

use Generator;
use PHPUnit\Framework\TestCase;
use PhpBench\Expression\Ast\IntegerNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\StringNode;
use PhpBench\Expression\Ast\VariableNode;
use PhpBench\Expression\Exception\EvaluationError;
use PhpBench\Expression\NodeEvaluator\VariableEvaluator;
use PhpBench\Tests\Unit\Expression\EvaluatorTestCase;

class VariableEvaluatorTest extends EvaluatorTestCase
{
    public function testExceptionIfParameterNotFound(): void
    {
        $this->expectException(EvaluationError::class);
        $this->expectExceptionMessage('not found');
        $this->evaluateNode(new VariableNode('foobar'), []);
    }

    /**
     * @dataProvider provideEvaluate
     * @param parameters $params
     */
    public function testEvaluate(string $name, array $params, Node $expected): void
    {
        $this->expectException(EvaluationError::class);
        $this->expectExceptionMessage('not found');
        $this->evaluateNode(new VariableNode('foobar'), []);
    }

    /**
     * @return Generator<mixed>
     */
    public function provideEvaluate(): Generator
    {
        yield [
            'foobar', 
            [
                'foobar' => 10,
            ],
            new IntegerNode(10)
        ];

        yield [
            'foobar', 
            [
                'foobar' => '10',
            ],
            new StringNode('10')
        ];
    }
}
