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

namespace PhpBench\Tests\Unit\Storage\Driver\Xml;

use PhpBench\Dom\Document;
use PhpBench\Model\Suite;
use PhpBench\Model\SuiteCollection;
use PhpBench\Serializer\XmlDecoder;
use PhpBench\Serializer\XmlEncoder;
use PhpBench\Storage\Driver\Xml\HistoryIterator;
use PhpBench\Storage\Driver\Xml\XmlDriver;
use PhpBench\Tests\TestCase;
use Prophecy\Argument;
use Symfony\Component\Filesystem\Filesystem;

class XmlDriverTest extends TestCase
{
    private $xmlEncoder;
    private $xmlDecoder;
    private $filesystem;
    private $collection;
    private $suite;

    /**
     */
    private $document;

    private XmlDriver $driver;

    protected function setUp(): void
    {
        $this->xmlEncoder = $this->prophesize(XmlEncoder::class);
        $this->xmlDecoder = $this->prophesize(XmlDecoder::class);
        $this->filesystem = $this->prophesize(Filesystem::class);

        $this->driver = new XmlDriver(
            '/path/to',
            $this->xmlEncoder->reveal(),
            $this->xmlDecoder->reveal(),
            $this->filesystem->reveal()
        );

        $this->collection = $this->prophesize(SuiteCollection::class);
        $this->suite = $this->prophesize(Suite::class);
        $this->document = $this->prophesize(Document::class);
    }

    /**
     * It should store a suite collection.
     */
    public function testStore(): void
    {
        $this->collection->getSuites()->willReturn([
            $this->suite->reveal(),
        ]);
        $this->suite->getUuid()->willReturn($uuid = '1339f38b191b77e1185f9729eb25a2aa4e262b01');
        $this->filesystem->exists('/path/to/7e0/3/c')->willReturn(true);
        $this->xmlEncoder->encode(Argument::type(SuiteCollection::class))->shouldBeCalled()
            ->willReturn($this->document->reveal());
        $this->document->save('/path/to/7e0/3/c/' . $uuid . '.xml')->shouldBeCalled();

        $this->driver->store($this->collection->reveal());
    }

    /**
     * It should create a non existing directory when storing the collection.
     */
    public function testStoreMkdir(): void
    {
        $this->collection->getSuites()->willReturn([
            $this->suite->reveal(),
        ]);
        $this->suite->getUuid()->willReturn($uuid = '1339f38b191b77e1185f9729eb25a2aa4e262b01');
        $this->filesystem->exists('/path/to/7e0/3/c')->willReturn(false);
        $this->filesystem->mkdir('/path/to/7e0/3/c')->shouldBeCalled();
        $this->xmlEncoder->encode(Argument::type(SuiteCollection::class))->shouldBeCalled()
            ->willReturn($this->document->reveal());
        $this->document->save('/path/to/7e0/3/c/' . $uuid . '.xml')->shouldBeCalled();

        $this->driver->store($this->collection->reveal());
    }

    /**
     * It should throw an exception if it cannot locate a given run by UUID.
     *
     */
    public function testFetch(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot find run with reference');
        $uuid = '1339f38b191b77e1185f9729eb25a2aa4e262b01';
        $this->filesystem->exists('/path/to/7e0/3/c/' . $uuid . '.xml')->willReturn(false);

        $this->driver->fetch($uuid);
    }

    /**
     * It should return true if ->has is called and the collection exists.
     */
    public function testHas(): void
    {
        $uuid = '1339f38b191b77e1185f9729eb25a2aa4e262b01';
        $this->filesystem->exists('/path/to/7e0/3/c/' . $uuid . '.xml')->willReturn(true);

        $this->assertTrue($this->driver->has($uuid));
    }

    /**
     * It should return false for `has` given an invalid UUID.
     */
    public function testHasInvalidUuid(): void
    {
        $uuid = '123';
        $this->assertFalse($this->driver->has($uuid));
    }

    /**
     * It should return the history iterator.
     */
    public function testGetHistoryIterator(): void
    {
        $history = $this->driver->history();
        $this->assertInstanceOf(HistoryIterator::class, $history);
    }
}
