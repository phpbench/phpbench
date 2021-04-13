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

namespace PhpBench\Tests\Unit\Environment\Provider;

use PhpBench\Environment\Provider;
use PhpBench\Remote\Launcher;
use PhpBench\Remote\Payload;
use PhpBench\Tests\TestCase;

class OpcacheTest extends TestCase
{
    private $launcher;
    private $payload;

    protected function setUp(): void
    {
        $this->launcher = $this->prophesize(Launcher::class);
        $this->payload = $this->prophesize(Payload::class);

        if (false === extension_loaded('Zend OPcache')) {
            $this->markTestSkipped();

            return;
        }
    }

    /**
     * Provider is always applicable.
     */
    public function testIsApplicable(): void
    {
        $this->assertTrue($this->createProvider(false)->isApplicable());
    }

    /**
     * It should provide the PHP version.
     */
    public function testExtensionLoaded(): void
    {
        $info = $this->createProvider(false)->getInformation();
        $this->assertEquals(true, $info['extension_loaded']);
    }

    public function testExtensionDisabledOnCli(): void
    {
        $info = $this->createProvider(false)->getInformation();
        $this->assertEquals(false, $info['enabled']);
    }

    private function createProvider(bool $opcacheEnabled)
    {
        $phpConfig = [
            'opcache.enable_cli' => $opcacheEnabled,
        ];

        $launcher = new Launcher(null, null, null, null, $phpConfig);

        return new Provider\Opcache($launcher);
    }
}
