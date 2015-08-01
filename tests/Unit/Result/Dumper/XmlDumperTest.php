<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Unit\Result\Dumper;

use PhpBench\Result\Dumper\XmlDumper;
use PhpBench\Result\SuiteResult;
use PhpBench\Result\BenchmarkResult;
use PhpBench\Result\SubjectResult;
use PhpBench\Result\IterationsResult;
use PhpBench\Result\IterationResult;

class XmlDumperTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->xmlDumper = new XmlDumper();
    }

    /**
     * It should serialize the suite result to an XML file.
     */
    public function testDump()
    {
        $expected = <<<EOT
  <suite>
    <benchmark class="Benchmark\Class">
      <subject identifier="1" name="mySubject">
        <parameter name="foo" value="bar"/>
        <parameter name="array" multiple="1">
          <parameter name="0" value="one"/>
          <parameter name="1" value="two"/>
        </parameter>
        <parameter name="assoc_array" multiple="1">
          <parameter name="one" value="two"/>
          <parameter name="three" value="four"/>
        </parameter>
        <group name="one"/>
        <group name="two"/>
        <iterations>
          <iteration revs="1" time="100"/>
        </iterations>
      </subject>
    </benchmark>
  </suite>
EOT;

        $suite = $this->getSuite();
        $dom = $this->xmlDumper->dump($suite);
        $this->assertNotNull($dom);
        $this->assertInstanceOf('DOMDocument', $dom);
        $result = $dom->saveXml();
        $this->assertContains($expected, $result);

        return $result;
    }

    protected function getSuite()
    {
        $iteration1 = new IterationResult(array('revs' => 1, 'time' => 100));
        $iterations = new IterationsResult(array($iteration1));
        $subject = new SubjectResult(
            1,
            'mySubject',
            array('one', 'two'),
            array(
                'foo' => 'bar',
                'array' => array('one', 'two'),
                'assoc_array' => array(
                    'one' => 'two',
                    'three' => 'four',
                ),
            ),
            array($iterations)
        );
        $benchmark = new BenchmarkResult('Benchmark\Class', array($subject));
        $suite = new SuiteResult(array($benchmark));

        return $suite;
    }
}
