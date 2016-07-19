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

namespace PhpBench\Extensions\XDebug\Tests\System;

use PhpBench\Tests\System\SystemTestCase;
use Symfony\Component\Filesystem\Filesystem;

class XDebugTestCase extends SystemTestCase
{
    public function setUp()
    {
        if (!extension_loaded('xdebug')) {
            $this->markTestSkipped('XDebug not enabled.');
        }

        $this->clean();
    }

    public function tearDown()
    {
        $this->clean();
    }

    private function clean()
    {
        if (file_exists($profileDir = $this->getWorkingDir('xdebug'))) {
            $filesystem = new Filesystem();
            $filesystem->remove($profileDir);
        }

        if (file_exists($profileDir = $this->getWorkingDir('foobar'))) {
            $filesystem = new Filesystem();
            $filesystem->remove($profileDir);
        }
    }

    public function phpbench($command, $workingDir = '.')
    {
        $command .= ' --extension="PhpBench\\Extensions\\XDebug\\XDebugExtension"';

        return parent::phpbench($command, $workingDir);
    }
}
