<?php

namespace PhpBench\Tests;

use PhpBench\Tests\Util\Workspace;

class IntegrationTestCase extends TestCase
{
    protected static function workspace(): Workspace
    {
        return Workspace::create(__DIR__ . '/Workspace');
    }
}
