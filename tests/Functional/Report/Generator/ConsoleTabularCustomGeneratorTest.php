<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Functional\Report\Generator;

use PhpBench\Benchmark\SuiteDocument;

class ConsoleTabularCustomGeneratorTest extends ConsoleTestCase
{
    protected function getGenerator()
    {
        $generator = $this->getContainer()->get('report_generator.tabular_custom');
        $generator->setOutput($this->getOutput());

        return $generator;
    }

    /**
     * It should generate a report from a Tabular JSON file.
     */
    public function testDefault()
    {
        $this->generate(
            $this->getSuiteDocument(),
            array('file' => __DIR__ . '/reports/test.json')
        );

        $output = $this->getOutput()->fetch();
        $this->assertContains('This is a test', $output);
    }

    /**
     * It should throw an exception if the file is not found.
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage not found
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

    private function getSuiteDocument()
    {
        $suite = new SuiteDocument();
        $suite->loadXml(<<<EOT
<?xml version="1.0"?>
<phpbench version="0.x">
    <benchmark class="Foobar">
        <subject name="mySubject">
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
</phpbench>
EOT
        );

        return $suite;
    }
}
