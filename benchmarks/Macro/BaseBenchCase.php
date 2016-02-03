<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Benchmarks\Macro;

use PhpBench\DependencyInjection\Container;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Base class for PHPBench macro benchmarks.
 *
 * These benchmarks use seconds as the time unit.
 *
 * @OutputTimeUnit("seconds")
 * @Iterations(10)
 * @BeforeClassMethods({"createWorkspace"}, extend=true)
 * @AfterClassMethods({"removeWorkspace"})
 */
class BaseBenchCase
{
    private $container;

    /**
     * The constructor can be used as a quick way to setup the
     * class and as an alternative to explicitly declaring methods
     * to execute before the benchmark.
     */
    public function __construct()
    {
        $this->container = $this->getContainer();
    }

    /**
     * This method is called in a separate process before the iterations
     * are executed. See the annotations in the header of this class.
     */
    public static function createWorkspace()
    {
        if (!file_exists(self::getWorkspacePath())) {
            mkdir(self::getWorkspacePath());
        }
    }

    /**
     * This method is called in a separate process after the iterations
     * are executed. See the annotations in the header of this class.
     */
    public static function removeWorkspace()
    {
        $filesystem = new Filesystem();
        if (file_exists(self::getWorkspacePath())) {
            $filesystem->remove(self::getWorkspacePath());
        }
    }

    protected function getContainer()
    {
        $container = new Container(array(
            'PhpBench\Extension\CoreExtension',
        ));
        $container->init();

        return $container;
    }

    public function runCommand($serviceId, $args)
    {
        $input = new ArrayInput($args);
        $output = new BufferedOutput();
        $command = $this->getContainer()->get($serviceId);
        $exitCode = $command->run($input, $output);

        if ($exitCode !== 0) {
            throw new \RuntimeException(sprintf(
                'Got non-zero exit code when executing command "%s"',
                $serviceId
            ));
        }

        return $output;
    }

    protected static function getFunctionalBenchmarkPath()
    {
        return __DIR__ . '/../../tests/Functional/benchmarks';
    }

    protected static function getWorkspacePath()
    {
        return __DIR__ . '/_workspace';
    }
}
