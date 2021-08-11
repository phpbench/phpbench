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

use PhpBench\Storage\UuidResolver\ChainResolver;
use PhpBench\Storage\UuidResolverInterface;
use PhpBench\Tests\TestCase;
use Prophecy\Prophecy\ObjectProphecy;

class ChainResolverTest extends TestCase
{
    public const TEST_REFERENCE = '1234';
    public const TEST_UUID = 'uuid';

    /**
     * @var UuidResolverInterface|ObjectProphecy
     */
    private $resolver;

    protected function setUp(): void
    {
        $this->resolver = $this->prophesize(UuidResolverInterface::class);
    }

    public function testNullIfNoResolverIntervened(): void
    {
        $chainResolver = new ChainResolver([]);
        $this->assertEquals(null, $chainResolver->resolve(self::TEST_REFERENCE));
    }

    public function testChainResolve(): void
    {
        $chainResolver = new ChainResolver([$this->resolver->reveal()]);
        $this->resolver->resolve(self::TEST_REFERENCE)->willReturn(self::TEST_UUID);

        $uuid = $chainResolver->resolve(self::TEST_REFERENCE);

        $this->assertEquals(self::TEST_UUID, $uuid);
    }

    public function testNullWhenChainResolveNoSupport(): void
    {
        $chainResolver = new ChainResolver([$this->resolver->reveal()]);
        $this->resolver->resolve(self::TEST_REFERENCE)->willReturn(null);

        $uuid = $chainResolver->resolve(self::TEST_REFERENCE);

        $this->assertEquals(null, $uuid);
    }
}
