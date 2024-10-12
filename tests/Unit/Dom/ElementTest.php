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

class ElementTest extends TestCase
{
    private $element;
    private $document;

    protected function setUp(): void
    {
        $this->document = new Document();
        $this->element = $this->document->createRoot('test');
    }

    /**
     * It should create and append a child element.
     */
    public function testAppendElement(): void
    {
        $element = $this->element->appendElement('hello');
        $result = $this->document->evaluate('count(//hello)');
        $this->assertInstanceOf('PhpBench\Dom\Element', $element);
        $this->assertEquals(1, $result);
    }

    /**
     * It should create and append text.
     */
    public function testAppendTextNode(): void
    {
        $element = $this->element->appendTextNode('hello', 'fix&foxy');
        $result = $this->document->evaluate('count(//hello)');
        $this->assertInstanceOf('PhpBench\Dom\Element', $element);
        $this->assertEquals(1, $result);
    }

    /**
     * It should exeucte an XPath query.
     */
    public function testQuery(): void
    {
        $boo = $this->element->appendElement('boo');
        $nodeList = $this->element->query('.//*');
        $this->assertInstanceOf('DOMNodeList', $nodeList);
        $this->assertEquals(1, $nodeList->length);
        $nodeList = $boo->query('.//*');
        $this->assertEquals(0, $nodeList->length);
    }

    /**
     * It should evaluate an XPath expression.
     */
    public function testEvaluate(): void
    {
        $boo = $this->element->appendElement('boo');
        $count = $this->element->evaluate('count(.//*)');
        $this->assertEquals(1, $count);
        $count = $boo->evaluate('count(.//*)');
        $this->assertEquals(0, $count);
    }

    /**
     * It should query for one element.
     */
    public function testQueryOne(): void
    {
        $boo = $this->element->appendElement('boo');
        $node = $this->element->queryOne('./boo');
        $this->assertSame($boo, $node);
    }

    /**
     * It should return null if one element is queried for an it none exist.
     */
    public function testQueryOneNone(): void
    {
        $node = $this->element->queryOne('./boo');
        $this->assertNull($node);
    }

    /**
     * It should return the XML contained in the node.
     */
    public function testDumpNode(): void
    {
        $this->element->appendElement('boo');
        $dump = $this->element->dump();

        $this->assertEquals(<<<EOT
<?xml version="1.0"?>
<test>
  <boo/>
</test>

EOT
            , $dump);
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
