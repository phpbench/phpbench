<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Unit\Report\Dom;

use PhpBench\Dom\Document;

class DocumentTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->dom = new Document();
    }

    /**
     * It should create a root node.
     */
    public function testCreateRoot()
    {
        $this->dom->createRoot('foobar');
        $this->assertXml(<<<EOT
<?xml version="1.0"?>
<foobar/>
EOT
        , $this->dom->saveXml());
    }

    /**
     * It should execute an xpath query.
     */
    public function testXPath()
    {
        $dom = $this->createDocument();
        $result = $dom->xpath()->evaluate('count(//table)');
        $this->assertEquals(2, $result);
    }

    /**
     * Elements should be of type Element.
     */
    public function testElement()
    {
        $element = $this->dom->createRoot('foobar');
        $this->assertInstanceOf('PhpBench\Dom\Element', $element);
    }

    /**
     * Elements should append Elements.
     */
    public function testElementAppend()
    {
        $element = $this->dom->createRoot('foobar');
        $element->appendElement('barfoo');
        $this->assertXml(<<<EOT
<?xml version="1.0"?>
<foobar>
  <barfoo/>
</foobar>
EOT
        , $this->dom->saveXml());
    }

    /**
     * Elements can have expression executed on them.
     */
    public function testElementXPathExpression()
    {
        $dom = $this->createDocument();
        foreach ($dom->xpath()->query('//table') as $tableEl) {
            $this->assertEquals(2, $tableEl->evaluate('count(./row)'));
        }
    }

    /**
     * Elements can have queries executed on them.
     */
    public function testElementXPathQuery()
    {
        $dom = $this->createDocument();
        foreach ($dom->xpath()->query('//table') as $tableEl) {
            $rowEls = $tableEl->query('./row');
            $this->assertEquals(2, $rowEls->length);
        }
    }

    private function assertXml($expected, $actual)
    {
        $this->assertEquals(trim($expected), trim($actual));
    }

    private function createDocument()
    {
        $xml = <<<EOT
<?xml version="1.0"?>
<report>
    <title>Hello</title>
    <table>
        <row/>
        <row/>
    </table>
    <table>
        <row/>
        <row/>
    </table>
</report>
EOT;

        $dom = new Document();
        $dom->loadXml($xml);

        return $dom;
    }
}
