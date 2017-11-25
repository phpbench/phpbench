<?php

namespace PhpBench\Extensions\Elastic\Tests\Unit\Driver;

use PHPUnit\Framework\TestCase;
use PhpBench\Storage\DriverInterface;
use PhpBench\Extensions\Elastic\Driver\ElasticDriver;
use PhpBench\Tests\Util\TestUtil;
use PhpBench\Model\SuiteCollection;
use PhpBench\Extensions\Elastic\Encoder\DocumentEncoder;
use PhpBench\Extensions\Elastic\Driver\ElasticClient;
use PhpBench\Expression\Constraint\Comparison;
use PhpBench\Expression\Constraint\Constraint;
use PhpBench\Storage\HistoryIteratorInterface;

class ElasticDriverTest extends TestCase
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
     * @var ElasticDriver
     */
    private $driver;

    /**
     * @var ObjectProphecy
     */
    private $encoder;

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
        $this->client = $this->prophesize(ElasticClient::class);
        $this->encoder = $this->prophesize(DocumentEncoder::class);

        $this->driver = new ElasticDriver(
            $this->client->reveal(),
            $this->innerDriver->reveal(),
            $this->encoder->reveal()
        );

        $this->constraint = $this->prophesize(Constraint::class);
        $this->history = $this->prophesize(HistoryIteratorInterface::class);
    }

    public function testStore()
    {
        $suite = TestUtil::createSuite();
        $collection = new SuiteCollection([$suite]);
        $document = [ 'field' => 'value' ];

        $this->encoder->documentsFromSuite($suite)->willReturn([
            'one' => $document
        ]);
        $this->client->put('one', $document)->shouldBeCalled();

        $this->innerDriver->store($collection)->shouldBeCalled();

        $this->driver->store($collection);
    }

    public function testDecoration()
    {
        $collection = new SuiteCollection();
        $suiteId = 1234;

        $this->innerDriver->query($this->constraint->reveal())->willReturn($collection);
        $return = $this->driver->query($this->constraint->reveal());
        $this->assertSame($return, $collection);

        $this->innerDriver->fetch($suiteId)->willReturn($collection);
        $return = $this->driver->fetch($suiteId);
        $this->assertSame($collection, $return);

        $this->innerDriver->has($suiteId)->willReturn(true);
        $return = $this->driver->has($suiteId);
        $this->assertTrue($return);

        $this->innerDriver->delete($suiteId)->shouldBeCalled();
        $return = $this->driver->delete($suiteId);

        $this->innerDriver->history()->willReturn($this->history->reveal());
        $return = $this->driver->history();
        $this->assertSame($this->history->reveal(), $return);
    }
}
