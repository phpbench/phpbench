<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Unit\Dom;

use PhpBench\Dom\Document;
use PhpBench\Dom\functions as functions;

require_once __DIR__ . '/../../../lib/Dom/xpath_functions.php';

class xpath_functionsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Parameters to JSON should convert a DOMNodeList to a JSON encoded string.
     */
    public function testParamsToJSON()
    {
        $dom = $this->getSuiteDocument();
        $parameters = $dom->xpath()->query('//suite[1]//variant/parameter');
        $json = functions\parameters_to_json($parameters);
        $array = json_decode($json, true);

        $this->assertInternalType('array', $array);
        $this->assertEquals(array(
            'foo' => 'bar',
            'array' => array(
                0 => 'one',
                1 => 'two',
            ),
            'assoc_array' => array(
                'one' => 'two',
                'three' => 'four',
            ),
        ), $array);
    }

    /**
     * It should select corresponding nodes from a different suite.
     */
    public function testCorrespondingNodes()
    {
        $dom = $this->getSuiteDocument();
        $nodes = $dom->xpath()->query('//suite[1]//iteration');
        $fragment = functions\suite($nodes, 'Implementation 2');

        $this->assertEquals(2, $fragment->childNodes->length);
        $this->assertEquals(500, $fragment->childNodes->item(0)->getAttribute('time'));
        $this->assertEquals(175, $fragment->childNodes->item(1)->getAttribute('time'));
    }

    private function getSuiteDocument()
    {
        $suite = new Document();
        $suite->loadXml(<<<'EOT'
<?xml version="1.0"?>
<phpbench version="0.x">
    <suite context="Implementation 1">
        <benchmark class="Foobar">
            <subject name="mySubject">
                <group name="one" />
                <group name="two" />
                <group name="three" />
                <variant>
                    <parameter name="foo" value="bar" />
                    <parameter name="array" type="collection">
                        <parameter name="0" value="one" />
                        <parameter name="1" value="two" />
                    </parameter>
                    <parameter name="assoc_array" type="collection">
                        <parameter name="one" value="two" />
                        <parameter name="three" value="four" />
                    </parameter>
                    <iteration time="100" memory="100" revs="1" />
                    <iteration time="75" memory="100" revs="1" />
               </variant>
            </subject>
        </benchmark>
    </suite>
    <suite context="Implementation 2">
        <benchmark class="Foobar">
            <subject name="mySubject">
                <group name="one" />
                <group name="two" />
                <group name="three" />
                <variant>
                    <iteration time="500" memory="100" revs="1" />
                    <iteration time="175" memory="100" revs="1" />
               </variant>
            </subject>
        </benchmark>
    </suite>
</phpbench>
EOT
        );

        return $suite;
    }
}
