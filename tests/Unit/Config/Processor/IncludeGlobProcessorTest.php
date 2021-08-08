<?php

namespace PhpBench\Tests\Unit\Config\Processor;

use PhpBench\Config\ConfigLoader;
use PhpBench\Config\Linter\SeldLinter;
use PhpBench\Config\Processor\IncludeGlobProcessor;
use PhpBench\Tests\IntegrationTestCase;

class IncludeGlobProcessorTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        $this->workspace()->reset();
    }

    public function testNoDirective(): void
    {
        $this->workspace()->put('test', json_encode([
            'one' => 'two',
        ]));
        self::assertEquals([
            'one' => 'two',
        ], $this->createLoader()->load($this->workspace()->path('test')));
    }

    public function testIncludeGlob(): void
    {
        $this->workspace()->put('test', json_encode([
            'one' => 'two',
            '$include-glob' => '*.json',
        ]));
        $this->workspace()->put('three.json', json_encode([
            'three' => 3,
        ]));
        self::assertEquals([
            'one' => 'two',
            'three' => 3,
        ], $this->createLoader()->load($this->workspace()->path('test')));
    }

    public function testDeepMergeConfigurations(): void
    {
        $this->workspace()->put('test', json_encode([
            'generators' => [
                'generatorx' => [],
            ],
            '$include-glob' => 'one/generator*.json',
        ]));
        $this->workspace()->put('one/generator1.json', json_encode([
            'generators' => [
                'generator1' => [],
            ],
        ]));
        $this->workspace()->put('one/generator2.json', json_encode([
            'generators' => [
                'generator2' => [],
            ],
        ]));
        self::assertEquals([
            'generators' => [
                'generatorx' => [],
                'generator1' => [],
                'generator2' => [],
            ],
        ], $this->createLoader()->load($this->workspace()->path('test')));
    }

    private function createLoader(): ConfigLoader
    {
        return new ConfigLoader(new SeldLinter(), [new IncludeGlobProcessor()]);
    }
}
