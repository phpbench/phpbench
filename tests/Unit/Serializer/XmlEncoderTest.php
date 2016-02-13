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

use PhpBench\PhpBench;
use PhpBench\Serializer\XmlEncoder;

class XmlEncoderTest extends XmlTestCase
{
    public function setUp()
    {
        $this->suiteCollection = $this->prophesize('PhpBench\Model\SuiteCollection');
        $this->suite = $this->prophesize('PhpBench\Model\Suite');
        $this->env1 = $this->prophesize('PhpBench\Environment\Information');
        $this->bench1 = $this->prophesize('PhpBench\Model\Benchmark');
        $this->subject1 = $this->prophesize('PhpBench\Model\Subject');
        $this->variant1 = $this->prophesize('PhpBench\Model\Variant');
        $this->iteration1 = $this->prophesize('PhpBench\Model\Iteration');
    }

    /**
     * It should encode the suite to an XML document.
     *
     * @dataProvider provideEncode
     */
    public function testEncode(array $params, $expected)
    {
        $expected = str_replace('PHPBENCH_VERSION', PhpBench::VERSION, $expected);
        $collection = $this->getSuiteCollection($params);
        $xmlEncoder = new XmlEncoder();
        $dom = $xmlEncoder->encode($collection);
        $this->assertInstanceOf('PhpBench\Dom\Document', $dom);
        $this->assertEquals($expected, $dom->dump());
    }
}
