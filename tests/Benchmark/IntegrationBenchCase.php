<?php

namespace PhpBench\Tests\Benchmark;

use PhpBench\DependencyInjection\Container;
use PhpBench\Extension\CoreExtension;
use PhpBench\Extension\ExpressionExtension;
use Psr\Container\ContainerInterface;

abstract class IntegrationBenchCase
{
    protected function container(): ContainerInterface
    {
        $container = new Container([
            CoreExtension::class,
            ExpressionExtension::class
        ]);
        $container->init();

        return $container;
    }
}
