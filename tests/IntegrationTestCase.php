<?php

namespace PhpBench\Tests;

use PHPUnit\Framework\TestCase;
use PhpBench\Tests\Util\Workspace;

class IntegrationTestCase extends TestCase
{
    protected function workspace(): Workspace
    {
        return Workspace::create(__DIR__ . '/Workspace');
    }
}
