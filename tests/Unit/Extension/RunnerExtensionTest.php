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
use PhpBench\Extension\RunnerExtension;
use PhpBench\Tests\IntegrationTestCase;

class RunnerExtensionTest extends IntegrationTestCase
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
        $container = $this->container([
            'path' => 'hello',
            'config_path' => '/path/to/phpbench.json',
        ]);
        $this->assertEquals(['/path/to/hello'], $container->getParameter('path'));
    }

    /**
     * It should automatically switch to the travis logger if the
     * CONTINUOUS_INTEGRATION environment variable is set.
     */
    public function testTravisLogger(): void
    {
        putenv('CONTINUOUS_INTEGRATION=1');

        $container = $this->container([
            'path' => 'hello',
            'config_path' => '/path/to/phpbench.json',
        ]);
        $this->assertEquals('travis', $container->getParameter('progress'));
    }

    public function testConfigDriver(): void
    {
        $container = $this->container([
            RunnerExtension::PARAM_RUNNER_ASSERT => 'foobar',
            RunnerExtension::PARAM_RUNNER_EXECUTOR => 'foobar',
            RunnerExtension::PARAM_RUNNER_FORMAT => 'foobar',
            RunnerExtension::PARAM_RUNNER_ITERATIONS => 12,
            RunnerExtension::PARAM_RUNNER_OUTPUT_MODE => 'mode',
            RunnerExtension::PARAM_RUNNER_OUTPUT_TIME_UNIT => 'foobar',
            RunnerExtension::PARAM_RUNNER_REVS => 32,
            RunnerExtension::PARAM_RUNNER_TIMEOUT => 12,
            RunnerExtension::PARAM_RUNNER_WARMUP => 12,
        ]);
        $container->get(ConfigDriver::class);
        $this->addToAssertionCount(1);
    }
}
