<?php

/*
 * This file is part of the PhpBench DOM  package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Dom\Tests\Unit;

use PhpBench\Dom\Document;
use PhpBench\Dom\Exception\InvalidQueryException;
use PHPUnit\Framework\TestCase;

class XPathTest extends TestCase
{
    /**
     * It should throw an exception if the xpath query is invalid.
     */
    public function testQueryException(): void
    {
        $this->expectException(InvalidQueryException::class);
        $this->getDocument()->query('//article[noexistfunc() = "as"]');
    }

    /**
     * It should NOT throw an exception if the expression evaluates as false.
     */
    public function testEvaluateFalse(): void
    {
        $result = $this->getDocument()->evaluate('boolean(count(//foo))');
        $this->assertFalse($result);
    }

    private function getDocument()
    {
        $xml = <<<EOT
<?xml version="1.0"?>
<document>
    <article id="1">
        <title>Morning</title>
    </article>
    <article id="2">
        <title>Afternoon</title>
    </article>
</document>
EOT;

        $document = new Document();
        $document->loadXml($xml);

        return $document;
    }
}
