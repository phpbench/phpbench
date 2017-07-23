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

namespace PhpBench\Tests\Unit\Storage;

use PhpBench\Registry\Registry;
use PhpBench\Storage\DriverInterface;
use PhpBench\Storage\HistoryEntry;
use PhpBench\Storage\HistoryIteratorInterface;
use PhpBench\Storage\UuidResolver;
use PHPUnit\Framework\TestCase;

class UuidResolverTest extends TestCase
{
    private $resolver;
    private $storage;
    private $history;
    private $historyEntry;
    private $historyEntry1;

    public function setUp()
    {
        $registry = $this->prophesize(Registry::class);
        $this->storage = $this->prophesize(DriverInterface::class);
        $registry->getService()->willReturn($this->storage->reveal());
        $this->history = $this->prophesize(HistoryIteratorInterface::class);
        $this->historyEntry = $this->prophesize(HistoryEntry::class);
        $this->historyEntry1 = $this->prophesize(HistoryEntry::class);

        $this->resolver = new UuidResolver(
            $registry->reveal()
        );
    }

    /**
     * It should resove the "latest" token.
     */
    public function testResolveLatest()
    {
        $this->storage->history()->willReturn($this->history->reveal());
        $this->history->current()->willReturn($this->historyEntry->reveal());
        $this->historyEntry->getRunId()->willReturn(1234);

        $uuid = $this->resolver->resolve('latest');
        $this->assertEquals(1234, $uuid);
    }

    /**
     * It should return the given UUID it is not a token.
     */
    public function testResolveLatestNotKeyword()
    {
        $this->storage->history()->shouldNotBeCalled();

        $uuid = $this->resolver->resolve(1234);
        $this->assertEquals(1234, $uuid);
    }

    /**
     * It should return the nth history run using the minus operator.
     */
    public function testLatestMinusNth()
    {
        $this->storage->history()->willReturn($this->history->reveal());
        $this->history->current()->willReturn(
            $this->historyEntry->reveal(),
            $this->historyEntry->reveal(),
            $this->historyEntry1->reveal()
        );
        $this->historyEntry->getRunId()->willReturn(1234);
        $this->historyEntry1->getRunId()->willReturn(4321);

        $this->history->next()->shouldBeCalledTimes(3);

        $uuid = $this->resolver->resolve('latest-2');
        $this->assertEquals(4321, $uuid);
    }

    /**
     * It should throw an exception if no history is present.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage No history present
     */
    public function testNoHistory()
    {
        $this->storage->history()->willReturn($this->history->reveal());
        $this->history->current()->willReturn(false);

        $this->resolver->resolve('latest');
    }
}
