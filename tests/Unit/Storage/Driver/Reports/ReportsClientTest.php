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

class ReportsClientTest extends TestCase
{
    /**
     * @var ObjectProphecy
     */
    private $transport;

    /**
     * @var ObjectProphecy
     */
    private $elasticEncoder;

    /**
     * @var ReportClient
     */
    private $client;

    public function setUp()
    {
        $this->transport = $this->prophesize(TransportInterface::class);
        $this->elasticEncoder = $this->prophesize(ElasticEncoder::class);
    }

    public function testPostNoStoreIterations()
    {
        $testElastic = ['one' => 'two'];
        $suite = TestUtil::createSuite();
        $this->elasticEncoder->aggregationsFromSuite($suite)->willReturn($testElastic);
        $this->transport->post('/suite', $testElastic)->shouldBeCalled();

        $this->createClient(false)->post($suite);
    }

    public function testStoreIterations()
    {
        $testElastic = ['one' => 'two'];
        $suite = TestUtil::createSuite();
        $this->elasticEncoder->aggregationsFromSuite($suite)->willReturn($testElastic);
        $this->transport->post('/suite', $testElastic)->shouldBeCalled();

        $this->elasticEncoder->iterationsFromSuite($suite)->willReturn($testElastic);
        $this->transport->post('/iterations', $testElastic)->shouldBeCalled();

        $this->createClient(true)->post($suite);
    }

    private function createClient(bool $storeIterations): ReportsClient
    {
        return new ReportsClient(
            $this->transport->reveal(),
            $this->elasticEncoder->reveal(),
            $storeIterations
        );
    }
}
