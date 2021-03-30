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

use PhpBench\Benchmark\Metadata\Driver\ConfigDriver;
use PhpBench\DependencyInjection\Container;
use PhpBench\Extension\CoreExtension;
use PhpBench\Tests\IntegrationTestCase;

class CoreExtensionTest extends IntegrationTestCase
{
    protected function tearDown(): void
    {
        putenv('CONTINUOUS_INTEGRATION=0');
    }

    /**
     * It should expand the "path" parameter to an absolute path if it is relative.
     */
    public function testRelativizePath(): void
    {
        $container = new Container(['PhpBench\Extension\CoreExtension'], [
            'path' => 'hello',
            'config_path' => '/path/to/phpbench.json',
        ]);
        $container->init();
        $this->assertEquals(['/path/to/hello'], $container->getParameter('path'));
    }

    /**
     * It should automatically switch to the travis logger if the
     * CONTINUOUS_INTEGRATION environment variable is set.
     */
    public function testTravisLogger(): void
    {
        putenv('CONTINUOUS_INTEGRATION=1');

        $container = new Container(['PhpBench\Extension\CoreExtension'], [
            'path' => 'hello',
            'config_path' => '/path/to/phpbench.json',
        ]);
        $container->init();
        $this->assertEquals('travis', $container->getParameter('progress'));
    }

    public function testConfigDriver(): void
    {
        $container = $this->container([
            CoreExtension::PARAM_RUNNER_ASSERT => 'foobar',
            CoreExtension::PARAM_RUNNER_EXECUTOR => 'foobar',
            CoreExtension::PARAM_RUNNER_FORMAT => 'foobar',
            CoreExtension::PARAM_RUNNER_ITERATIONS => 12,
            CoreExtension::PARAM_RUNNER_OUTPUT_MODE => 'mode',
            CoreExtension::PARAM_RUNNER_OUTPUT_TIME_UNIT => 'foobar',
            CoreExtension::PARAM_RUNNER_REVS => 32,
            CoreExtension::PARAM_RUNNER_TIMEOUT => 12,
            CoreExtension::PARAM_RUNNER_WARMUP => 12,
        ]);
        $container->get(ConfigDriver::class);
        $this->addToAssertionCount(1);
    }
}
