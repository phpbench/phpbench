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

use PhpBench\Dom\SuiteDocument;

class SuiteDocumentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * It should append documents.
     */
    public function testAppendSuiteDocument()
    {
        $suiteDocument = $this->getSuiteDocument('base');
        $this->assertEquals(1, $suiteDocument->evaluate('count(//benchmark)'));
        $newSuiteDocument = $this->getSuiteDocument('foo');
        $suiteDocument->appendSuiteDocument($newSuiteDocument, 'foo');
        $this->assertEquals(2, $suiteDocument->evaluate('count(//benchmark)'));
        $this->assertEquals('foo', $suiteDocument->evaluate('string(/phpbench/suite[position()=2]/@context)'));
    }

    /**
     * It should throw an exception if the append target has no phpbench root element.
     *
     * @expectedException InvalidArgumentException
     */
    public function testAppendSuiteDocumentException()
    {
        $suiteDocument = new SuiteDocument();
        $newSuiteDocument = $this->getSuiteDocument('arf');
        $suiteDocument->appendSuiteDocument($newSuiteDocument, 'foo');
    }

    private function getSuiteDocument($name)
    {
        $doc = new SuiteDocument();
        $rootEl = $doc->createRoot('phpbench');
        $suiteEl = $rootEl->appendElement('suite');
        $suiteEl->setAttribute('context', $name);
        $suiteEl->appendElement('benchmark');

        return $doc;
    }
}
