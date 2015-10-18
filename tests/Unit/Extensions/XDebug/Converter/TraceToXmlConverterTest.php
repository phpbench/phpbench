<?php

namespace PhpBench\Tests\Unit\Extensions\XDebug\Converter;

use PhpBench\Extensions\XDebug\Converter\TraceToXmlConverter;

class TraceToXmlConverterTest extends \PHPUnit_Framework_TestCase
{
    private $converter;

    public function setUp()
    {
        $this->converter = new TraceToXmlConverter();
    }

    /**
     * It should transform an XML function trace to a DOM document.
     */
    public function testTransformToXml()
    {
        $dom = $this->converter->convert(__DIR__ . '/trace_to_xml/trace1');
        $this->assertEquals(13, $dom->xpath()->evaluate('count(//entry)'));
    }
}
