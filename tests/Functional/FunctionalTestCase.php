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

namespace PhpBench\Tests\Functional;

use PhpBench\DependencyInjection\Container;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * NOTE: This is currently used only be the DBAL functional tests.
 */
class FunctionalTestCase extends TestCase
{
    private $container;

    /**
     * TODO: option to disable the cache is here because there is a bug
     * in the Runner/Builder which aggregates benchmarks on multiple runs.
     */
    protected function getContainer($cache = true, $config = [])
    {
        if ($cache && $this->container) {
            return $this->container;
        }

        $this->container = new Container([
            'PhpBench\Extension\CoreExtension',
        ], $config);
        $this->container->init();

        return $this->container;
    }

    protected function getWorkspacePath()
    {
        $path = sys_get_temp_dir() . '/phpbench_test_workspace';

        return $path;
    }

    protected function cleanWorkspace()
    {
        $filesystem = new Filesystem();
        $path = $this->getWorkspacePath();

        if (file_exists($path)) {
            $filesystem->remove($path);
        }
    }

    protected function initWorkspace()
    {
        $this->cleanWorkspace();
        $filesystem = new Filesystem();
        $filesystem->mkdir($this->getWorkspacePath());
    }
}
