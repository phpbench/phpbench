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

namespace PhpBench\Tests\Unit\Extension;

use PhpBench\DependencyInjection\Container;
use PHPUnit\Framework\TestCase;

class CoreExtensionTest extends TestCase
{
    protected function tearDown(): void
    {
        putenv('CONTINUOUS_INTEGRATION=0');
    }

    /**
     * It should expand the "path" parameter to an absolute path if it is relative.
     */
    public function testRelativizePath()
    {
        $container = new Container(['PhpBench\Extension\CoreExtension'], [
            'path' => 'hello',
            'config_path' => '/path/to/phpbench.json',
        ]);
        $container->init();
        $this->assertEquals('/path/to/hello', $container->getParameter('path'));
    }

    /**
     * It should automatically switch to the travis logger if the
     * CONTINUOUS_INTEGRATION environment variable is set.
     */
    public function testTravisLogger()
    {
        putenv('CONTINUOUS_INTEGRATION=1');

        $container = new Container(['PhpBench\Extension\CoreExtension'], [
            'path' => 'hello',
            'config_path' => '/path/to/phpbench.json',
        ]);
        $container->init();
        $this->assertEquals('travis', $container->getParameter('progress'));
    }
}
