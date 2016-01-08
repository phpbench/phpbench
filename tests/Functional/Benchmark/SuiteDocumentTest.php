<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Functional\Benchmark;

use PhpBench\Benchmark\SuiteDocument;
use PhpBench\Tests\Functional\FunctionalTestCase;

class SuiteDocumentTest extends FunctionalTestCase
{
    /**
     * It should return the min, max and mean values.
     */
    public function testMinMaxMean()
    {
        $suiteDocument = $this->getSuiteDocument();
        $this->assertEquals(2.73, round($suiteDocument->getMeanTime(), 2));
        $this->assertEquals(2.0, round($suiteDocument->getMinTime(), 2));
        $this->assertEquals(20, round($suiteDocument->getMaxTime(), 2));
    }

    /**
     * It should append documents.
     */
    public function testAppendSuiteDocument()
    {
        $suiteDocument = $this->getSuiteDocument();
        $this->assertEquals(1, $suiteDocument->evaluate('count(//benchmark)'));
        $newSuiteDocument = $this->getSuiteDocument('BarBench.php');
        $suiteDocument->appendSuiteDocument($newSuiteDocument, 'foo');
        $this->assertEquals(2, $suiteDocument->evaluate('count(//benchmark)'));
        $this->assertEquals('foo', $suiteDocument->evaluate('string(/phpbench/suite[position()=2]/@name)'));
    }

    /**
     * It should throw an exception if the append target has no phpbench root element.
     *
     * @expectedException InvalidArgumentException
     */
    public function testAppendSuiteDocumentException()
    {
        $suiteDocument = new SuiteDocument();
        $newSuiteDocument = $this->getSuiteDocument();
        $suiteDocument->appendSuiteDocument($newSuiteDocument, 'foo');
    }

    /**
     * It should not evaluate things to 0 when no iterations are present.
     */
    public function testMinMaxMeanNoIterations()
    {
        $suiteDocument = $this->getSuiteDocument('EmptyBench.php');

        $this->assertEquals(0, $suiteDocument->getMinTime());
        $this->assertEquals(0, $suiteDocument->getMaxTime());
        $this->assertEquals(0, $suiteDocument->getMeanTime());
    }
}
