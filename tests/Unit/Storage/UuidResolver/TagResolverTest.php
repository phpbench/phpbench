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

use PhpBench\Model\Tag;
use PhpBench\Storage\Driver\Fake\FakeHistoryIterator;
use PhpBench\Storage\DriverInterface;
use PhpBench\Storage\HistoryEntry;
use PhpBench\Storage\StorageRegistry;
use PhpBench\Storage\UuidResolver\TagResolver;
use PhpBench\Tests\TestCase;

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
        $this->historyEntry = $this->prophesize(HistoryEntry::class);
        $this->historyEntry1 = $this->prophesize(HistoryEntry::class);
        $this->history = new FakeHistoryIterator(
            $this->historyEntry->reveal(),
            $this->historyEntry1->reveal()
        );
        $registry = $this->prophesize(StorageRegistry::class);
        $registry->getService()->willReturn($this->storage->reveal());
        $this->storage->history()->willReturn($this->history);

        $this->resolver = new TagResolver($registry->reveal());
    }

    public function testThrowsExceptionWhenNoTagFound(): void
    {
        self::assertNull($this->resolver->resolve('1asd3foobar123'));
    }

    /**
     * @dataProvider provideTags
     */
    public function testReturnsUuidForLatestTag(string $tag): void
    {
        $this->historyEntry->getTag()->willReturn(new Tag($tag));
        $this->historyEntry->getRunId()->willReturn(1234);

        $uuid = $this->resolver->resolve($tag);
        $this->assertEquals(1234, $uuid);
    }

    public static function provideTags(): array
    {
        return [
            [ 'foobar',],
            [ '1234',],
            [ 'php74',],
        ];
    }

    public function testReturnsUuidForTagWithMatchingTagAtOffset(): void
    {
        $this->historyEntry->getTag()->willReturn(new Tag('foobar'));
        $this->historyEntry1->getTag()->willReturn(new Tag('foobar'));
        $this->historyEntry1->getRunId()->willReturn(1234);

        $uuid = $this->resolver->resolve('foobar-1');
        $this->assertEquals(1234, $uuid);
    }
}
