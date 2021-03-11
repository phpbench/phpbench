<?php

namespace PhpBench\Tests;

use PhpBench\DependencyInjection\Container;
use PhpBench\Extension\CoreExtension;
use PhpBench\Extension\ExpressionExtension;
use PhpBench\Tests\Util\Workspace;

class IntegrationTestCase extends TestCase
{
    protected static function workspace(): Workspace
    {
        return Workspace::create(__DIR__ . '/Workspace');
    }

    protected function container(): Container
    {
        return (function (Container $container) {
            $container->init();

            return $container;
        })(new Container([
            ExpressionExtension::class,
            CoreExtension::class
        ], [
            ExpressionExtension::PARAM_SYNTAX_HIGHLIGHTING => false,
        ]));
    }
}
