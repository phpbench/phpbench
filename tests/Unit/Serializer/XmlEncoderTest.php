<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace PhpBench\Tests\Unit\Serializer;

use PhpBench\Dom\Document;
use PhpBench\Model\Suite;
use PhpBench\Model\SuiteCollection;
use PhpBench\PhpBench;
use PhpBench\Serializer\XmlEncoder;
use PhpBench\Tests\Util\Approval;
use PhpBench\Tests\Util\SuiteBuilder;
use RuntimeException;

class XmlEncoderTest extends XmlTestCase
{
    /**
     * It should encode the suite to an XML document.
     *
     * @dataProvider provideEncode
     */
    public function testEncode(string $path): void
    {
        $approval = Approval::create($path, 2);
        $params = $approval->getConfig(0);

        $collection = $this->getSuiteCollection($params);
        $dom = $this->encode($collection);
        $approval->approve($this->dumpNormalized($dom));
    }

    public function doTestBinary(SuiteCollection $collection): void
    {
        $approval = Approval::create(__DIR__ . '/examples/binary1.example', 0);
        $dom = $this->encode($collection);
        $approval->approve($this->dumpNormalized($dom));
    }

    public function doTestDate(SuiteCollection $collection): void
    {
        $approval = Approval::create(__DIR__ . '/examples/date1.example', 0);
        $dom = $this->encode($collection);
        $approval->approve($this->dumpNormalized($dom));
    }

    public function testUnserizableParameter(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot serialize');
        $collection = $this->getSuiteCollection([
            'params' => [
                'invalid' => function (): void {
                }
            ],
        ]);
        $this->encode($collection);
    }

    public function testParameters(): void
    {
        $collection = new SuiteCollection([
            SuiteBuilder::create('one')->withDateString('2021-01-01')->benchmark('bench')->subject('subject')->variant()->withParameterSet('one', [
                'int' => 1,
                'float' => 1.123,
                'string' => 'string',
            ])->end()->end()->end()->build()
        ]);
        $approval = Approval::create(__DIR__ . '/examples/parameters.example', 0);
        $dom = $this->encode($collection);
        $approval->approve($this->dumpNormalized($dom));
    }

    private function dumpNormalized(Document $dom)
    {
        return str_replace(PhpBench::version(), 'PHPBENCH_VERSION', $dom->dump());
    }

    private function encode(SuiteCollection $collection): Document
    {
        $xmlEncoder = new XmlEncoder();

        return $xmlEncoder->encode($collection);
    }
}
