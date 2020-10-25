<?php

namespace PhpBench\Tests;

use PhpBench\Tests\Util\Workspace;
use PhpBench\Tests\TestCase;

class IntegrationTestCase extends TestCase
{
    protected static function workspace(): Workspace
    {
        return Workspace::create(__DIR__ . '/Workspace');
    }
}
