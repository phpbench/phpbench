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
use PHPUnit\Framework\TestCase;

class UnixSysloadTest extends TestCase
{
    private $provider;

    protected function setUp(): void
    {
        if (stristr(PHP_OS, 'win')) {
            $this->markTestSkipped('Unix specific test');
        }

        $this->provider = new Provider\UnixSysload();
    }

    /**
     * Provider is always applicable because this test only runs
     * in Unix environments.
     */
    public function testIsApplicable()
    {
        $this->assertTrue($this->provider->isApplicable());
    }

    /**
     * It should provide the load averages.
     */
    public function testUnixSysloadVersion()
    {
        $info = $this->provider->getInformation();
        $this->assertArrayHasKey('l1', $info);
        $this->assertArrayHasKey('l5', $info);
        $this->assertArrayHasKey('l15', $info);
    }
}
