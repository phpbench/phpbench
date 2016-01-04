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

class TabularCustomGeneratorTest extends GeneratorTestCase
{
    /**
     * It should generate a report from a Tabular JSON file.
     */
    public function testDefault()
    {
        $dom = $this->generate(
            $this->getSuiteDocument(),
            array('file' => __DIR__ . '/reports/test.json')
        );

        $this->assertInstanceOf('PhpBench\Dom\Document', $dom);
        $this->assertContains('This is a test', $dom->xpath()->evaluate('string(//cell)'));
    }

    /**
     * It should throw an exception if the file is not found.
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage not exist
     */
    public function testNotFoundFile()
    {
        $this->generate(
            $this->getSuiteDocument(),
            array('file' => __DIR__ . '/reports/not_existing_test.json')
        );
    }

    /**
     * It should thow an exception if no file is provided.
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage You must provide the path
     */
    public function testNoPathGiven()
    {
        $this->generate(
            $this->getSuiteDocument(),
            array()
        );
    }

    protected function getGenerator()
    {
        $generator = $this->getContainer()->get('report_generator.tabular_custom');

        return $generator;
    }
}
