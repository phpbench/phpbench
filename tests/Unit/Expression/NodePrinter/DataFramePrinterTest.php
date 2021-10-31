<?php

namespace PhpBench\Tests\Unit\Expression\NodePrinter;

use PhpBench\Data\DataFrame;
use PhpBench\Expression\Ast\DataFrameNode;
use PhpBench\Tests\Unit\Expression\NodePrinterTestCase;

class DataFramePrinterTest extends NodePrinterTestCase
{
    public function testPrintDataFrame(): void
    {
        self::assertEquals('[frame cols=0 rows=0]', $this->print(new DataFrameNode(DataFrame::empty())));
    }
}
