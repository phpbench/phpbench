<?php

namespace PhpBench\Tests;

use PhpBench\DependencyInjection\Container;
use PhpBench\Extension\ConsoleExtension;
use PhpBench\Extension\CoreExtension;
use PhpBench\Extension\ExpressionExtension;
use PhpBench\Extension\ReportExtension;
use PhpBench\Extension\RunnerExtension;
use PhpBench\Extension\StorageExtension;
use PhpBench\Tests\Util\Workspace;

class IntegrationTestCase extends TestCase
{
    protected static function workspace(): Workspace
    {
        return Workspace::create(__DIR__ . '/Workspace');
    }

    protected function container(array $config = []): Container
    {
        return (function (Container $container) {
            $container->init();

            return $container;
        })(new Container([
            ExpressionExtension::class,
            CoreExtension::class,
            ReportExtension::class,
            StorageExtension::class,
            RunnerExtension::class,
            ConsoleExtension::class,
        ], array_merge([
            ExpressionExtension::PARAM_SYNTAX_HIGHLIGHTING => false,
        ], $config)));
    }
}
