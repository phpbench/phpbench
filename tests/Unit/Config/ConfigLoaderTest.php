<?php

namespace PhpBench\Tests\Unit\Config;

use PHPUnit\Framework\TestCase;
use PhpBench\Config\ConfigLoader;
use PhpBench\Config\Exception\ConfigFileNotFound;
use PhpBench\Tests\IntegrationTestCase;

class ConfigLoaderTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        $this->workspace()->reset();
    }

    public function testLoadsConfig(): void
    {
        $this->workspace()->put('test.json', '{"foobar": "barfoo"}');
        self::assertEquals(
            ['foobar' => 'barfoo'],
            ConfigLoader::create()->load($this->workspace()->path('test.json'))
        );
    }

    public function testExceptionOnFileNotFound(): void
    {
        $this->expectException(ConfigFileNotFound::class);
        self::assertEquals(
            ['foobar' => 'barfoo'],
            ConfigLoader::create()->load($this->workspace()->path('test.json'))
        );
    }
}
