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

use PhpBench\Environment\Provider;

class PhpTest extends \PHPUnit_Framework_TestCase
{
    private $provider;

    public function setUp()
    {
        $this->provider = new Provider\Php();
    }

    /**
     * Provider is always applicable.
     */
    public function testIsApplicable()
    {
        $this->assertTrue($this->provider->isApplicable());
    }

    /**
     * It should provide the PHP version.
     */
    public function testPhpVersion()
    {
        $info = $this->provider->getInformation();
        $this->assertEquals(PHP_VERSION, $info['version']);
    }
}
