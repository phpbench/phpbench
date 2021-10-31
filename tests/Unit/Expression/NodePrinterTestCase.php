<?php

namespace PhpBench\Tests\Unit\Expression;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Printer;
use PhpBench\Tests\IntegrationTestCase;

class NodePrinterTestCase extends IntegrationTestCase
{
    /**
     * @param parameters $params
     */
    public function print(Node $node, array $params = [], array $config = []): string
    {
        return $this->container($config)->get(Printer::class)->print($node, $params);
    }
}
