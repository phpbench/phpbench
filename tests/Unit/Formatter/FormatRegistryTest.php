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

use PhpBench\Formatter\FormatInterface;
use PhpBench\Formatter\FormatRegistry;
use PHPUnit\Framework\TestCase;

class FormatRegistryTest extends TestCase
{
    private $registry;
    private $format;

    public function setUp()
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
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Formatter with name
     */
    public function testRegisterExisting()
    {
        $this->registry->register('hello', $this->format->reveal());
        $this->registry->register('hello', $this->format->reveal());
    }

    /**
     * It should throw an exception if an unknown formatter is requiested.
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Unknown format
     */
    public function testUnknownFormatter()
    {
        $this->registry->get('hello');
    }
}
