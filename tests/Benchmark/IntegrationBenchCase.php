<?php

namespace PhpBench\Tests\Benchmark;

use PhpBench\DependencyInjection\Container;
use PhpBench\Extension\ConsoleExtension;
use PhpBench\Extension\CoreExtension;
use PhpBench\Extension\ExpressionExtension;
use PhpBench\Extension\ReportExtension;
use PhpBench\Extension\RunnerExtension;
use PhpBench\Extension\StorageExtension;
use PhpBench\Tests\Util\Workspace;
use Psr\Container\ContainerInterface;

abstract class IntegrationBenchCase
{
    protected function workspace(): Workspace
    {
        return Workspace::create(__DIR__ . '/../Workspace');
    }

    protected function container(array $config = []): ContainerInterface
    {
        $container = new Container([
            CoreExtension::class,
            ConsoleExtension::class,
            RunnerExtension::class,
            ReportExtension::class,
            StorageExtension::class,
            ExpressionExtension::class
        ], array_merge([
            ConsoleExtension::PARAM_DISABLE_OUTPUT => true,
        ], $config));
        $container->init();

        return $container;
    }
}
