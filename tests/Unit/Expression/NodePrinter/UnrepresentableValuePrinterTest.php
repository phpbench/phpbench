<?php

namespace PhpBench\Tests\Unit\Expression\NodePrinter;

use PhpBench\Expression\Ast\NullNode;
use PhpBench\Expression\Ast\UnrepresentableValueNode;
use PhpBench\Expression\NodePrinter\UnrepresentableValuePrinter;
use PhpBench\Expression\Printer;
use PhpBench\Tests\ProphecyTrait;
use PHPUnit\Framework\TestCase;

class UnrepresentableValuePrinterTest extends TestCase
{
    use ProphecyTrait;

    public function testPrint(): void
    {
        $printer = $this->prophesize(Printer::class);
        self::assertEquals('<string>', (new UnrepresentableValuePrinter())->print(
            $printer->reveal(),
            new UnrepresentableValueNode('asd')
        ));
    }

    public function testDoesNotPrint(): void
    {
        $printer = $this->prophesize(Printer::class);
        self::assertNull((new UnrepresentableValuePrinter())->print(
            $printer->reveal(),
            new NullNode()
        ));
    }
}
