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

namespace PhpBench\Examples\Benchmark\Macro;

use PhpBench\DependencyInjection\Container;
use PhpBench\Extension\ConsoleExtension;
use PhpBench\Extension\CoreExtension;
use PhpBench\Extension\ExpressionExtension;
use PhpBench\Extension\ReportExtension;
use PhpBench\Extension\RunnerExtension;
use PhpBench\Extension\StorageExtension;
use PhpBench\Tests\Util\Workspace;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * Base class for PHPBench macro benchmarks.
 *
 * These benchmarks use seconds as the time unit.
 *
 * @OutputTimeUnit("seconds", precision=3)
 * @Iterations(10)
 * @Warmup(1)
 * @BeforeClassMethods({"resetWorkspace"})
 * @AfterClassMethods({"removeWorkspace"})
 */
class BaseBenchCase
{
    private $container;

    private $extensions = [
        CoreExtension::class,
        RunnerExtension::class,
        ReportExtension::class,
        StorageExtension::class,
        ExpressionExtension::class,
        ConsoleExtension::class,
    ];

    private $config = [];

    /**
     * The constructor can be used as a quick way to setup the
     * class and as an alternative to explicitly declaring methods
     * to execute before the benchmark.
     */
    public function __construct()
    {
        $this->container = $this->getContainer();
    }

    protected static function workspace(): Workspace
    {
        return Workspace::create(__DIR__ . '/../../../tests/Workspace');
    }

    /**
     * This method is called in a separate process before the iterations
     * are executed. See the annotations in the header of this class.
     */
    public static function resetWorkspace()
    {
        self::workspace()->reset();
    }

    /**
     * This method is called in a separate process after the iterations
     * are executed. See the annotations in the header of this class.
     */
    public static function removeWorkspace()
    {
        // do something
    }

    protected function getContainer()
    {
        $container = new Container($this->extensions, array_merge([
            ConsoleExtension::PARAM_DISABLE_OUTPUT => true,
        ], $this->config));
        $container->init();

        return $container;
    }

    public function runCommand($serviceId, $args)
    {
        $input = new ArrayInput($args);
        $output = new BufferedOutput();
        $command = $this->getContainer([
            CoreExtension::PARAM_WORKING_DIR => $this->workspace()->path()
        ])->get($serviceId);
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
        return __DIR__ . '/benchmarks';
    }

    protected function addContainerExtensionClass($extensionClass)
    {
        $this->extensions[] = $extensionClass;
    }

    protected function setContainerConfig(array $containerConfig)
    {
        $this->config = $containerConfig;
    }
}
