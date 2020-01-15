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
use PHPUnit\Framework\TestCase;

class ChainResolverTest extends TestCase
{
    const TEST_REFERENCE = '1234';
    const TEST_UUID = 'uuid';

    /**
     * @var UuidResolverInterface|ObjectProphecy
     */
    private $resolver;

    protected function setUp(): void
    {
        $this->resolver = $this->prophesize(UuidResolverInterface::class);
    }

    public function testReturnsUuidIfNoResolverIntervened()
    {
        $chainResolver = new ChainResolver([]);
        $this->assertEquals(self::TEST_REFERENCE, $chainResolver->resolve(self::TEST_REFERENCE));
    }

    public function testChainResolve()
    {
        $chainResolver = new ChainResolver([$this->resolver->reveal()]);
        $this->resolver->supports(self::TEST_REFERENCE)->willReturn(true);
        $this->resolver->resolve(self::TEST_REFERENCE)->willReturn(self::TEST_UUID);

        $uuid = $chainResolver->resolve(self::TEST_REFERENCE);

        $this->assertEquals(self::TEST_UUID, $uuid);
    }

    public function testChainResolveNoSupport()
    {
        $chainResolver = new ChainResolver([$this->resolver->reveal()]);
        $this->resolver->supports(self::TEST_REFERENCE)->willReturn(false);
        $this->resolver->resolve(self::TEST_REFERENCE)->shouldNotBeCalled();

        $uuid = $chainResolver->resolve(self::TEST_REFERENCE);

        $this->assertEquals(self::TEST_REFERENCE, $uuid);
    }
}
