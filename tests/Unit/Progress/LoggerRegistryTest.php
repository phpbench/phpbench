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

namespace PhpBench\Tests\Unit;

use InvalidArgumentException;
use PhpBench\Progress\LoggerInterface;
use PhpBench\Progress\LoggerRegistry;
use PhpBench\Tests\TestCase;
use Prophecy\Prophecy\ObjectProphecy;

class LoggerRegistryTest extends TestCase
{
    private $registry;

    /**
     * @var ObjectProphecy<LoggerInterface>
     */
    private $progressLogger;


    protected function setUp(): void
    {
        $this->registry = new LoggerRegistry();
        $this->progressLogger = $this->prophesize(LoggerInterface::class);
    }

    /**
     * It should register progress loggers.
     */
    public function testRegisterProgressLogger(): void
    {
        $this->registry->addProgressLogger('foobar', $this->progressLogger->reveal());
        $logger = $this->registry->getProgressLogger('foobar');
        $this->assertSame($this->progressLogger->reveal(), $logger);
    }

    /**
     * It should throw an exception when a non-existing logger is requested.
     *
     */
    public function testUnknownLogger(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No progress logger with name "barfoo" has been registered, known progress loggers: "foobar"');
        $this->registry->addProgressLogger('foobar', $this->progressLogger->reveal());
        $this->registry->getProgressLogger('barfoo');
    }
}
