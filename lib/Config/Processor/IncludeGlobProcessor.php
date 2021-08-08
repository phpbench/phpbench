<?php

namespace PhpBench\Config\Processor;

use PhpBench\Config\ConfigLoader;
use PhpBench\Config\ConfigProcessor;
use Webmozart\PathUtil\Path;

class IncludeGlobProcessor implements ConfigProcessor
{
    public const DIRECTIVE = '$include-glob';

    public function process(ConfigLoader $loader, string $path, array $config): array
    {
        if (!isset($config[self::DIRECTIVE])) {
            return $config;
        }

        foreach ((array)$config[self::DIRECTIVE] as $globPath) {
            $globPath = Path::makeAbsolute($globPath, dirname($path));
            foreach (glob($globPath) as $includePath) {
                $config = array_merge_recursive(
                    $config,
                    $loader->load($includePath)
                );
                unset($config[self::DIRECTIVE]);
            }
        }

        return $config;
    }

    public function testIncludeGlob(): void
    {
        $this->workspace()->put('test', json_encode([
            'one' => 'two',
            '$include' => [
                'one/*.json',
            ],
        ]));
        $this->workspace()->put('one/three.json', json_encode([
            'three' => 3,
        ]));
        $this->workspace()->put('one/four.json', json_encode([
            'four' => 4,
        ]));
        self::assertEquals([
            'one' => 'two',
            'three' => 3,
            'four' => 4,
        ], $this->createLoader()->load($this->workspace()->path('test')));
    }

    public function testDeepMergeConfigurations(): void
    {
        $this->workspace()->put('test', json_encode([
            'generators' => [
                'foobar' => [],
            ],
            '$include' => 'more_generators.json',
        ]));
        $this->workspace()->put('more_generators.json', json_encode([
            'generators' => [
                'barfoo' => [],
            ],
        ]));
        self::assertEquals([
            'generators' => [
                'foobar' => [],
                'barfoo' => [],
            ],
        ], $this->createLoader()->load($this->workspace()->path('test')));
    }
}
