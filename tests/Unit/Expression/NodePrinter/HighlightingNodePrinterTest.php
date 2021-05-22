<?php

namespace PhpBench\Tests\Unit\Expression\NodePrinter;

use Generator;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\NodePrinter;
use PhpBench\Expression\NodePrinter\HighlightingNodePrinter;
use PhpBench\Expression\Printer;
use PhpBench\Expression\Theme\ArrayTheme;
use PhpBench\Tests\ProphecyTrait;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class HighlightingNodePrinterTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @dataProvider providePrint
     */
    public function testPrint(Node $node, array $map, string $expected): void
    {
        $printer = $this->prophesize(Printer::class);
        $printer->print(Argument::type(Node::class));

        $nodePrinter = $this->prophesize(NodePrinter::class);
        $nodePrinter->print($printer, $node)->willReturn('test');

        self::assertEquals($expected, (new HighlightingNodePrinter(
            $nodePrinter->reveal(),
            new ArrayTheme($map)
        ))->print($printer->reveal(), $node));
    }

    /**
     * @return Generator<mixed>
     */
    public function providePrint(): Generator
    {
        yield 'decorates' => [
            new TestNode(),
            [
                TestNode::class => 'fg=foo'
            ],
            '<fg=foo>test</>',
        ];

        yield 'does not decorate non-mapped nodes' => [
            new NotMappedNode(),
            [
                TestNode::class => 'fg=foo'
            ],
            'test',
        ];

        yield 'evaluates callback' => [
            new TestNode(),
            [
                TestNode::class => function (Node $node) {
                    return 'fg=black';
                }
            ],
            '<fg=black>test</>',
        ];
    }
}

class NotMappedNode extends Node
{
}

class TestNode extends Node
{
}
