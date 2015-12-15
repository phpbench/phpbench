<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Unit\Extension;

use PhpBench\DependencyInjection\Container;
use PhpBench\Extension\CoreExtension;

class CoreExtensionTest extends \PHPUnit_Framework_TestCase
{
    private $container;
    private $extension;

    public function setUp()
    {
        $this->container = new Container();
        $this->extension = new CoreExtension();
    }

    public function tearDown()
    {
        putenv('CONTINUOUS_INTEGRATION=0');
    }

    /**
     * It should expand the "path" parameter to an absolute path if it is relative.
     */
    public function testRelativizePath()
    {
        $this->extension->configure($this->container);
        $this->container->mergeParameters(array(
            'path' => 'hello',
            'config_path' => '/path/to/phpbench.json',
        ));
        $this->extension->build($this->container);
        $this->assertEquals('/path/to/hello', $this->container->getParameter('path'));
    }

    /**
     * It should automatically switch to the travis logger if the
     * CONTINUOUS_INTEGRATION environment variable is set.
     */
    public function testTravisLogger()
    {
        putenv('CONTINUOUS_INTEGRATION=1');

        $this->extension->configure($this->container);
        $this->assertEquals('travis', $this->container->getParameter('progress'));
    }
}
