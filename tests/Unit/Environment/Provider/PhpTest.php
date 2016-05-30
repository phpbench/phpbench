<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Unit\Environment\Provider;

use PhpBench\Benchmark\Remote\Launcher;
use PhpBench\Benchmark\Remote\Payload;
use PhpBench\Environment\Provider;
use Prophecy\Argument;

class PhpTest extends \PHPUnit_Framework_TestCase
{
    private $launcher;
    private $payload;

    public function setUp()
    {
        $this->launcher = $this->prophesize(Launcher::class);
        $this->payload = $this->prophesize(Payload::class);
    }

    /**
     * Provider is always applicable.
     */
    public function testIsApplicable()
    {
        $this->assertTrue($this->createProvider()->isApplicable());
    }

    /**
     * It should provide the PHP version.
     */
    public function testPhpVersion()
    {
        $info = $this->createProvider()->getInformation();
        $this->assertEquals(PHP_VERSION, $info['version']);
    }

    /**
     * It should get the version from the remote process if the configured
     * PHP binary is different from the one being used to execute PhpBench.
     */
    public function testRemote()
    {
        $this->launcher->payload(Argument::type('string'), [])->willReturn($this->payload->reveal());
        $this->payload->launch()->willReturn(['version' => 'success']);
        $info = $this->createProvider(true)->getInformation();
        $this->assertEquals('success', $info['version']);
    }

    private function createProvider($remote = false)
    {
        return new Provider\Php(
            $this->launcher->reveal(),
            $remote
        );
    }
}
