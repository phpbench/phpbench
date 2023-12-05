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

use PhpBench\Environment\Provider\Php;
use PhpBench\Environment\Provider;
use PhpBench\Remote\Launcher;
use PhpBench\Tests\TestCase;

class PhpTest extends TestCase
{
    /**
     * Provider is always applicable.
     */
    public function testIsApplicable(): void
    {
        $this->assertTrue($this->createProvider()->isApplicable());
    }

    /**
     * It should provide the PHP version.
     */
    public function testPhpVersion(): void
    {
        $info = $this->createProvider()->getInformation();
        $this->assertEquals(PHP_VERSION, $info['version']);
    }

    /**
     * It should provide the path to the PHP ini file.
     */
    public function testPhpIni(): void
    {
        $info = $this->createProvider()->getInformation();
        $this->assertEquals(php_ini_loaded_file(), $info['ini']);
    }

    private function createProvider(): Php
    {
        return new Php(new Launcher());
    }
}
