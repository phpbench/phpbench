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

namespace PhpBench\Tests\Unit\Storage\Driver\Reports;

use PhpBench\Serializer\ElasticEncoder;
use PhpBench\Storage\Driver\Reports\ReportsClient;
use PhpBench\Storage\Driver\Reports\TransportInterface;
use PhpBench\Tests\Util\TestUtil;
use PHPUnit\Framework\TestCase;
use PhpBench\Serializer\XmlEncoder;
use PhpBench\Dom\Document;

class ReportsClientTest extends TestCase
{
    /**
     * @var TransportInterface|ObjectProphecy
     */
    private $transport;

    /**
     * @var XmlEncoder|ObjectProphecy
     */
    private $xmlEncoder;

    /**
     * @var ReportClient
     */
    private $client;

    /**
     * @var Document|ObjectProphecy
     */
    private $document;

    public function setUp()
    {
        $this->transport = $this->prophesize(TransportInterface::class);
        $this->xmlEncoder = $this->prophesize(XmlEncoder::class);
        $this->document = $this->prophesize(Document::class);
        $this->document->dump()->willReturn('asd');
    }

    public function testStoreIterations()
    {
        $testElastic = ['one' => 'two'];
        $collection = TestUtil::createCollection();
        $this->xmlEncoder->encode($collection)->willReturn($this->document);
        $this->transport->post('/import', 'asd')->shouldBeCalled();
        $this->createClient(true)->post($collection);
    }

    private function createClient(bool $storeIterations): ReportsClient
    {
        return new ReportsClient(
            $this->transport->reveal(),
            $this->xmlEncoder->reveal(),
            $storeIterations
        );
    }
}
