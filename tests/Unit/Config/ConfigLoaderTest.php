<?php

namespace PhpBench\Tests\Unit\Config;

use PhpBench\Config\ConfigLoader;
use PhpBench\Config\Exception\ConfigFileNotFound;
use PhpBench\Config\Linter\SeldLinter;
use PhpBench\Config\Processor\CallbackProcessor;
use PhpBench\Tests\IntegrationTestCase;
use PhpBench\Tests\ProphecyTrait;

class ConfigLoaderTest extends IntegrationTestCase
{
    use ProphecyTrait;

    protected function setUp(): void
    {
        $this->workspace()->reset();
    }

    public function testLoadsConfig(): void
    {
        $this->workspace()->put('test.json', '{"foobar": "barfoo"}');
        self::assertEquals(
            ['foobar' => 'barfoo'],
            $this->createLoader()->load($this->workspace()->path('test.json'))
        );
    }

    public function testExceptionOnFileNotFound(): void
    {
        $this->expectException(ConfigFileNotFound::class);
        self::assertEquals(
            ['foobar' => 'barfoo'],
            $this->createLoader()->load($this->workspace()->path('test.json'))
        );
    }

    public function testAppliesProcessors(): void
    {
        $this->workspace()->put('test.json', '{"foobar": "barfoo"}');

        $result = $this->createLoader([
            new CallbackProcessor(function (ConfigLoader $loader, string $path, array $config) {
                $config['barfoo'] = 'foobar';

                return $config;
            }),
        ])->load($this->workspace()->path('test.json'));

        self::assertEquals([
            'foobar' => 'barfoo',
            'barfoo' => 'foobar',
        ], $result);
    }

    public function testAppliesProcessorsThatApplyProcessors(): void
    {
        $this->workspace()->put('test.json', '{"foobar": "barfoo", "include": "extra.json"}');
        $this->workspace()->put('extra.json', '{"bar": "baz"}');

        $result = $this->createLoader([
            new CallbackProcessor(function (ConfigLoader $loader, string $path, array $config) {
                if (isset($config['include'])) {
                    $config['extra'] = $loader->load(dirname($path) . '/' . $config['include']);
                }
                unset($config['include']);

                return $config;
            }),
        ])->load($this->workspace()->path('test.json'));

        self::assertEquals([
            'foobar' => 'barfoo',
            'extra' => [
                'bar' => 'baz',
            ],
        ], $result);
    }

    private function createLoader(array $processors = []): ConfigLoader
    {
        return new ConfigLoader(new SeldLinter(), $processors);
    }
}
