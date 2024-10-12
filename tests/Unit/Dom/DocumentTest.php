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
use PHPUnit\Framework\TestCase;

class DocumentTest extends TestCase
{
    /**
     * @var Document
     */
    private $document;

    protected function setUp(): void
    {
        $this->document = new Document(1.0);
    }

    /**
     * It should perform an XPath query.
     */
    public function testQuery(): void
    {
        $this->document->loadXml($this->getXml());
        $nodeList = $this->document->query('//record');
        $this->assertInstanceOf('DOMNodeList', $nodeList);
        $this->assertEquals(2, $nodeList->length);
    }

    /**
     * It should evaluate an XPath expression.
     */
    public function testEvaluate(): void
    {
        $this->document->loadXml($this->getXml());
        $result = $this->document->evaluate('count(//record)');
        $this->assertEquals(2, $result);
    }

    /**
     * It should create a root element.
     */
    public function testCreateRoot(): void
    {
        $this->document->createRoot('hello');
        $this->assertStringContainsString('<hello/>', $this->document->saveXml());
    }

    /**
     * It should return a formatted string representation of the document.
     */
    public function testDump(): void
    {
        $this->document->loadXml($this->getXml());
        $this->assertEquals(
            trim($this->getXml()),
            trim($this->document->dump())
        );
    }

    /**
     * It should provide a duplicate version of itself.
     */
    public function testDuplicate(): void
    {
        $this->document->loadXml($this->getXml());
        $duplicate = $this->document->duplicate();
        $this->assertNotsame($this->document, $duplicate);
        $this->assertNotsame($this->document->firstChild, $duplicate->firstChild);
        $this->assertNotsame($this->document->firstChild->firstChild, $duplicate->firstChild->firstChild);
    }


    private function getXml()
    {
        $xml = <<<EOT
<?xml version="1.0"?>
<document>
    <record id="1">
        <title>Hello</title>
    </record>
    <record id="2">
        <title>World</title>
    </record>
</document>
EOT;

        return $xml;
    }
}
