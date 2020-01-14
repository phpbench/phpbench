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

namespace PhpBench\Tests\Unit\Storage\UuidResolver;

use InvalidArgumentException;
use PhpBench\Storage\DriverInterface;
use PhpBench\Storage\HistoryEntry;
use PhpBench\Storage\HistoryIteratorInterface;
use PhpBench\Storage\StorageRegistry;
use PhpBench\Storage\UuidResolver\TagResolver;
use PHPUnit\Framework\TestCase;

class TagResolverTest extends TestCase
{
    /**
     * @var ObjectProphecy
     */
    private $storage;

    /**
     * @var ObjectProphecy
     */
    private $history;

    /**
     * @var ObjectProphecy
     */
    private $historyEntry;

    /**
     * @var ObjectProphecy
     */
    private $historyEntry1;

    /**
     * @var TagResolver
     */
    private $resolver;

    protected function setUp(): void
    {
        $this->storage = $this->prophesize(DriverInterface::class);
        $this->history = $this->prophesize(HistoryIteratorInterface::class);
        $this->historyEntry = $this->prophesize(HistoryEntry::class);
        $this->historyEntry1 = $this->prophesize(HistoryEntry::class);
        $registry = $this->prophesize(StorageRegistry::class);
        $registry->getService()->willReturn($this->storage->reveal());
        $this->storage->history()->willReturn($this->history->reveal());

        $this->resolver = new TagResolver($registry->reveal());
    }

    public function testSupportsReferencesWithTagPrefix()
    {
        $this->assertTrue($this->resolver->supports('tag:asdf'));
        $this->assertFalse($this->resolver->supports('tag:'));
        $this->assertFalse($this->resolver->supports('test'));
    }

    public function testThrowsExceptionWhenNoTagFound()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->history->rewind()->shouldBeCalled();
        $this->history->valid()->willReturn(false);
        $this->resolver->resolve('tag:foobar');
    }

    public function testReturnsUuidForLatestTag()
    {
        $this->history->rewind()->shouldBeCalled();
        $this->history->valid()->willReturn(true);
        $this->history->current()->willReturn($this->historyEntry->reveal());

        $this->historyEntry->getTag()->willReturn('foobar');
        $this->historyEntry->getRunId()->willReturn(1234);

        $uuid = $this->resolver->resolve('tag:foobar');
        $this->assertEquals(1234, $uuid);
    }

    public function testReturnsUuidForTagWithMatchingTagAtOffset()
    {
        $this->history->rewind()->shouldBeCalled();
        $this->history->valid()->willReturn(true, true, true, false);
        $this->history->next()->shouldBeCalledTimes(2);
        $this->history->current()->willReturn($this->historyEntry->reveal());

        $this->historyEntry->getTag()->willReturn('foobar');
        $this->historyEntry->getRunId()->willReturn(1234);

        $uuid = $this->resolver->resolve('tag:foobar-2');
        $this->assertEquals(1234, $uuid);
    }

    public function testReturnsUuidForFirstTagAtOffset()
    {
        $this->history->rewind()->shouldBeCalled();
        $this->history->valid()->willReturn(true, true, true, true, false);
        $this->history->next()->shouldBeCalledTimes(3);
        $this->history->current()->willReturn($this->historyEntry->reveal());

        $this->historyEntry->getTag()->willReturn(null, 'foobar');
        $this->historyEntry->getRunId()->willReturn(1234);

        $uuid = $this->resolver->resolve('tag:foobar-2');
        $this->assertEquals(1234, $uuid);
    }
}
