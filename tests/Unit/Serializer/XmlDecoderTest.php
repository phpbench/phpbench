<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace PhpBench\Tests\Unit\Serializer;

use PhpBench\Dom\Document;
use PhpBench\Model\SuiteCollection;
use PhpBench\Serializer\XmlDecoder;
use PhpBench\Serializer\XmlEncoder;

class XmlDecoderTest extends XmlTestCase
{
    /**
     * It should encode the suite to an XML document.
     *
     * @dataProvider provideEncode
     */
    public function testDecoder(array $params, $expected)
    {
        $collection = $this->getSuiteCollection($params);
        $dom = $this->encode($collection);

        $decoder = new XmlDecoder();
        $collection = $decoder->decode($dom);

        $decodedDom = $this->encode($collection);

        $this->assertEquals(
            $dom->dump(),
            $decodedDom->dump()
        );
    }

    /**
     * It should throw an exception when encountering a non-existing result class.
     *
     */
    public function testDecodeUnknownResultClass()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('XML file defines a non-existing result class "FooVendor\FooResult" - maybe you are missing an extension?');
        $dom = new Document(1.0);
        $dom->loadXML(<<<EOT
<phpbench>
  <suite>
    <result key="foo" class="FooVendor\FooResult"/>
  </suite>
</phpbench>
EOT
        );
        $decoder = new XmlDecoder();
        $collection = $decoder->decode($dom);
    }

    /**
     * It should throw an exception for a non-existing result key.
     *
     */
    public function testDecodeUnknownResultKey()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No result class was provided with key "foobar" for attribute "foobar-foo"');
        $dom = new Document(1.0);
        $dom->loadXML(<<<EOT
<phpbench>
  <suite>
      <benchmark class="\PhpBench\Micro\Math\KdeBench">
      <subject name="benchKde">
        <variant>
          <iteration foobar-foo="12" />
        </variant>
      </subject>
      </benchmark>
  </suite>
</phpbench>
EOT
        );
        $decoder = new XmlDecoder();
        $collection = $decoder->decode($dom);
    }

    /**
     * It should throw an exception if an attribute name has no - prefix.
     *
     */
    public function testInvalidAttribute()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Expected attribute name to have a result key prefix, got "foo"');
        $dom = new Document(1.0);
        $dom->loadXML(<<<EOT
<phpbench>
  <suite>
      <benchmark class="\PhpBench\Micro\Math\KdeBench">
      <subject name="benchKde">
        <variant>
          <iteration foo="12" />
        </variant>
      </subject>
      </benchmark>
  </suite>
</phpbench>
EOT
        );
        $decoder = new XmlDecoder();
        $collection = $decoder->decode($dom);
    }

    private function encode(SuiteCollection $collection)
    {
        $xmlEncoder = new XmlEncoder();
        $dom = $xmlEncoder->encode($collection);

        return $dom;
    }
}
