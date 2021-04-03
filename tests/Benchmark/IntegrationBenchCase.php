<?php

namespace PhpBench\Tests\Benchmark;

use PhpBench\DependencyInjection\Container;
use PhpBench\Extension\CoreExtension;
use PhpBench\Extension\ExpressionExtension;
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
            ExpressionExtension::class
        ], $config);
        $container->init();

        return $container;
    }
}
