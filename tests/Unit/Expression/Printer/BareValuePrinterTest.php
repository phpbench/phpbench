<?php

namespace PhpBench\Tests\Unit\Expression\Printer;

use PhpBench\Expression\Ast\FunctionNode;
use PhpBench\Expression\Ast\IntegerNode;
use PhpBench\Expression\Ast\ListNode;
use PhpBench\Expression\Ast\NullNode;
use PhpBench\Expression\Ast\StringNode;
use PhpBench\Expression\Printer\BareValuePrinter;
use PHPUnit\Framework\TestCase;

class BareValuePrinterTest extends TestCase
{
    public function testPrintsBarePhpValue(): void
    {
        self::assertEquals('hello', $this->create()->print(new StringNode('hello'), []));
    }

    public function testReturnsDefaultTextIfNotPrintable(): void
    {
        self::assertEquals('??', $this->create()->print(new FunctionNode('hello'), []));
    }

    public function testCastsToString(): void
    {
        self::assertEquals('', $this->create()->print(new NullNode(), []));
    }

    public function testPrintsList(): void
    {
        self::assertEquals('[1, 2]', $this->create()->print(
            new ListNode([
                new IntegerNode(1),
                new IntegerNode(2),
            ]),
            []
        ));
    }

    private function create(): BareValuePrinter
    {
        return new BareValuePrinter();
    }
}
