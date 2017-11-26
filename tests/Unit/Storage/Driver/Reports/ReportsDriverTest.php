<?php

namespace PhpBench\Tests\Unit\Storage\Driver\Reports;

use PHPUnit\Framework\TestCase;
use PhpBench\Storage\DriverInterface;
use PhpBench\Extensions\Elastic\Driver\ElasticDriver;
use PhpBench\Tests\Util\TestUtil;
use PhpBench\Model\SuiteCollection;
use PhpBench\Serializer\ElasticEncoder;
use PhpBench\Expression\Constraint\Comparison;
use PhpBench\Expression\Constraint\Constraint;
use PhpBench\Storage\HistoryIteratorInterface;
use Prophecy\Prophecy\ObjectProphecy;
use PhpBench\Storage\Driver\Reports\ReportsDriver;
use PhpBench\Registry\Registry;
use PhpBench\Storage\Driver\Reports\ReportsClient;

class ReportsDriverTest extends TestCase
{
    /**
     * @var ObjectProphecy
     */
    private $innerDriver;

    /**
     * @var ObjectProphecy
     */
    private $client;

    /**
     * @var ReportsDriver
     */
    private $driver;

    /**
     * @var ObjectProphecy
     */
    private $constraint;

    /**
     * @var ObjectProphecy
     */
    private $history;

    public function setUp()
    {
        $this->innerDriver = $this->prophesize(DriverInterface::class);

        $this->constraint = $this->prophesize(Constraint::class);
        $this->history = $this->prophesize(HistoryIteratorInterface::class);
        $this->registry = $this->prophesize(Registry::class);
        $this->client = $this->prophesize(ReportsClient::class);
        $this->storageName = 'foobar';

        $this->registry->getService('foobar')->willReturn($this->innerDriver->reveal());
    }

    public function testStore()
    {
        $suite = TestUtil::createSuite();
        $collection = new SuiteCollection([$suite]);
        $document = [ 'field' => 'value' ];

        $this->client->post($suite);

        $this->innerDriver->store($collection)->shouldBeCalled();

        $this->createDriver()->store($collection);
    }

    public function testDecoration()
    {
        $collection = new SuiteCollection();
        $suiteId = 1234;
        $driver = $this->createDriver();

        $this->innerDriver->query($this->constraint->reveal())->willReturn($collection);
        $return = $driver->query($this->constraint->reveal());
        $this->assertSame($return, $collection);

        $this->innerDriver->fetch($suiteId)->willReturn($collection);
        $return = $driver->fetch($suiteId);
        $this->assertSame($collection, $return);

        $this->innerDriver->has($suiteId)->willReturn(true);
        $return = $driver->has($suiteId);
        $this->assertTrue($return);

        $this->innerDriver->delete($suiteId)->shouldBeCalled();
        $return = $driver->delete($suiteId);

        $this->innerDriver->history()->willReturn($this->history->reveal());
        $return = $driver->history();
        $this->assertSame($this->history->reveal(), $return);
    }

    private function createDriver(bool $storeIterations = false): ReportsDriver
    {
        return new ReportsDriver(
            $this->client->reveal(),
            $this->registry->reveal(),
            $this->storageName
        );
    }
}
