<?php

namespace PhpBench\Tests\Unit\Expression\NodePrinter;

use PhpBench\Data\DataFrame;
use PhpBench\Expression\Ast\DataFrameNode;
use PhpBench\Expression\Ast\DisplayAsNode;
use PhpBench\Expression\Ast\IntegerNode;
use PhpBench\Expression\Ast\StringNode;
use PhpBench\Expression\Ast\UnitNode;
use PhpBench\Extension\CoreExtension;
use PhpBench\Tests\Unit\Expression\NodePrinterTestCase;

class DisplayAsPrinterTest extends NodePrinterTestCase
{
    public function testPrintDataFrame(): void
    {
    }

    public function testDisplayAsBinaryUnit(): void
    {
        self::assertEquals('1.000GiB', $this->print(
            new DisplayAsNode(
                new IntegerNode(pow(1024, 3)),
                new UnitNode(new StringNode('memory'))
            ),
            [],
            [CoreExtension::PARAM_MEMORY_UNIT_AS_BINARY => true]
        ));
    }
}
