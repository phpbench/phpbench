<?php

namespace PhpBench\Tests\Unit;

use PhpBench\ProgressLoggerRegistry;

class ProgressLoggerRegistryTest extends \PHPUnit_Framework_TestCase
{
    private $registry;

    public function setUp()
    {
        $this->registry = new ProgressLoggerRegistry();
        $this->progressLogger = $this->prophesize('PhpBench\ProgressLoggerInterface');
    }

    /**
     * It should register progress loggers.
     */
    public function testRegisterProgressLogger()
    {
        $this->registry->addProgressLogger('foobar', $this->progressLogger->reveal());
        $logger = $this->registry->getProgressLogger('foobar');
        $this->assertSame($this->progressLogger->reveal(), $logger);
    }

    /**
     * It should throw an exception when a non-existing logger is requested.
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage No progress logger with name "barfoo" has been registered, known progress loggers: "foobar"
     */
    public function testUnknownLogger()
    {
        $this->registry->addProgressLogger('foobar', $this->progressLogger->reveal());
        $this->registry->getProgressLogger('barfoo');
    }
}
