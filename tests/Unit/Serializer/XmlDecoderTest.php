<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Unit\Serializer;

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

    private function encode(SuiteCollection $collection)
    {
        $xmlEncoder = new XmlEncoder();
        $dom = $xmlEncoder->encode($collection);

        return $dom;
    }
}
