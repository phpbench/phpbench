<?php

namespace PhpBench\Tests\Unit\Expression\NodePrinter;

use PhpBench\Expression\Ast\DisplayAsNode;
use PhpBench\Expression\Ast\IntegerNode;
use PhpBench\Expression\Ast\StringNode;
use PhpBench\Expression\Ast\UnitNode;
use PhpBench\Extension\ExpressionExtension;
use PhpBench\Tests\Unit\Expression\NodePrinterTestCase;

class DisplayAsPrinterTest extends NodePrinterTestCase
{
    public function testDisplayAsBinaryUnit(): void
    {
        self::assertEquals('1.000GiB', $this->print(
            new DisplayAsNode(
                new IntegerNode(pow(1024, 3)),
                new UnitNode(new StringNode('memory'))
            ),
            [],
            [ExpressionExtension::PARAM_MEMORY_UNIT_PREFIX => 'binary']
        ));
    }
}
