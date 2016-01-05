<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Functional\Report\Generator;

class HistogramGeneratorTest extends GeneratorTestCase
{
    protected function getGenerator()
    {
        $generator = $this->getContainer()->get('report_generator.histogram');

        return $generator;
    }

    /**
     * It should generate an iteration report by default.
     */
    public function testDefault()
    {
        $dom = $this->generate(
            $this->getSuiteDocument(),
            array()
        );

        $this->assertInstanceOf('PhpBench\Dom\Document', $dom);

        // 10 bins + 1
        $this->assertEquals(11, $dom->evaluate('count(//cell[@name="subject"][text()="benchMySubject"])'));
    }

    /**
     * It should allow different number of bins.
     */
    public function testBins()
    {
        $dom = $this->generate(
            $this->getSuiteDocument(),
            array(
                'bins' => 20,
            )
        );

        $this->assertInstanceOf('PhpBench\Dom\Document', $dom);

        // 20 bins + 1
        $this->assertEquals(21, $dom->evaluate('count(//cell[@name="subject"][text()="benchMySubject"])'));
    }
}
