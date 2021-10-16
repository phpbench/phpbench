<?php

namespace PhpBench\Tests\Unit\Expression\NodePrinter;

use PhpBench\Data\DataFrame;
use PhpBench\Expression\Ast\DataFrameNode;
use PhpBench\Expression\Ast\NullNode;
use PhpBench\Expression\Ast\UnrepresentableValueNode;
use PhpBench\Expression\NodePrinter\DataFramePrinter;
use PhpBench\Expression\NodePrinter\UnrepresentableValuePrinter;
use PhpBench\Expression\Printer;
use PhpBench\Tests\ProphecyTrait;
use PHPUnit\Framework\TestCase;
use PhpBench\Tests\Unit\Expression\NodePrinterTestCase;

class DataFramePrinterTest extends NodePrinterTestCase
{
    public function testPrintDataFrame(): void
    {
        self::assertEquals('[frame cols=0 rows=0]', $this->print(new DataFrameNode(DataFrame::empty())));
    }
}
