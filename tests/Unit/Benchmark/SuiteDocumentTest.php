<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Unit\Benchmark;

use PhpBench\Benchmark\SuiteDocument;

class SuiteDocumentTest extends \PHPUnit_Framework_TestCase
{
    private $suiteDocument;

    public function setUp()
    {
        $this->suiteDocument = new SuiteDocument();
    }

    /**
     * It should return the min, max and mean values.
     */
    public function testMinMaxMean()
    {
        $this->suiteDocument->load(__DIR__ . '/document/document.xml');
        $this->assertEquals(1, $this->suiteDocument->getMin());
        $this->assertEquals(2, $this->suiteDocument->getMax());
        $this->assertEquals(1.5, $this->suiteDocument->getMeanTime());
    }

    /**
     * It should append documents.
     */
    public function testAppendSuiteDocument()
    {
        $this->suiteDocument->load(__DIR__ . '/document/document.xml');
        $this->assertEquals(1, $this->suiteDocument->evaluate('count(//benchmark)'));
        $newSuiteDocument = new SuiteDocument();
        $newSuiteDocument->load(__DIR__ . '/document/document.xml');
        $this->suiteDocument->appendSuiteDocument($newSuiteDocument, 'foo');
        $this->assertEquals(2, $this->suiteDocument->evaluate('count(//benchmark)'));
        $this->assertEquals('foo', $this->suiteDocument->evaluate('string(/phpbench/suite[position()=2]/@name)'));
    }

    /**
     * It should throw an exception if the append target has no phpbench root element.
     *
     * @expectedException InvalidArgumentException
     */
    public function testAppendSuiteDocumentException()
    {
        $suiteDocument = new SuiteDocument();
        $newSuiteDocument = new SuiteDocument();
        $newSuiteDocument->load(__DIR__ . '/document/document.xml');
        $suiteDocument->appendSuiteDocument($newSuiteDocument, 'foo');
    }

    /**
     * It should not evaluate things to 0 when no iterations are present.
     */
    public function testMinMaxMeanNoIterations()
    {
        $this->suiteDocument->load(__DIR__ . '/document/empty.xml');
        $this->assertEquals(0, $this->suiteDocument->getMin());
        $this->assertEquals(0, $this->suiteDocument->getMax());
        $this->assertEquals(0, $this->suiteDocument->getMeanTime());
    }
}
