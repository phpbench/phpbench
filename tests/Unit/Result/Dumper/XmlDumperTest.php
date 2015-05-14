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
      <subject name="mySubject" description="My Subject's description">
        <iterations>
          <parameter name="foo" value="bar"/>
          <iteration time="100"/>
        </iterations>
      </subject>
    </benchmark>
  </suite>
EOT;

        $suite = $this->getSuite();
        $result = $this->xmlDumper->dump($suite);
        $this->assertContains($expected, $result);

        return $result;
    }

    protected function getSuite()
    {
        $iteration1 = new IterationResult(array('time' => 100));
        $iterations = new IterationsResult(array($iteration1), array('foo' => 'bar'));
        $subject = new SubjectResult('mySubject', 'My Subject\'s description', array($iterations));
        $benchmark = new BenchmarkResult('Benchmark\Class', array($subject));
        $suite = new SuiteResult(array($benchmark));

        return $suite;
    }
}
