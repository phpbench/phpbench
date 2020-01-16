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

namespace PhpBench\Tests\Unit\Formatter;

use InvalidArgumentException;
use PhpBench\Formatter\FormatInterface;
use PhpBench\Formatter\FormatRegistry;
use PHPUnit\Framework\TestCase;

class FormatRegistryTest extends TestCase
{
    private $registry;
    private $format;

    protected function setUp(): void
    {
        $this->registry = new FormatRegistry();
        $this->format = $this->prophesize(FormatInterface::class);
    }

    /**
     * It should register and retrieve formats.
     */
    public function testRegisterRetrieve()
    {
        $this->registry->register('hello', $this->format->reveal());
        $format = $this->registry->get('hello');
        $this->assertSame($this->format->reveal(), $format);
    }

    /**
     * It should throw an exception if an attempt is made to add a duplicate format.
     *
     */
    public function testRegisterExisting()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Formatter with name');
        $this->registry->register('hello', $this->format->reveal());
        $this->registry->register('hello', $this->format->reveal());
    }

    /**
     * It should throw an exception if an unknown formatter is requiested.
     *
     */
    public function testUnknownFormatter()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown format');
        $this->registry->get('hello');
    }
}
