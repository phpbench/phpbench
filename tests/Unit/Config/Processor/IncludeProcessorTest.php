<?php

namespace PhpBench\Tests\Unit\Config\Processor;

use PhpBench\Config\ConfigLoader;
use PhpBench\Config\Linter\SeldLinter;
use PhpBench\Config\Processor\IncludeProcessor;
use PhpBench\Tests\IntegrationTestCase;

class IncludeProcessorTest extends IntegrationTestCase
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

    public function testIncludeFile(): void
    {
        $this->workspace()->put('test', json_encode([
            'one' => 'two',
            '$include' => 'three.json',
        ]));
        $this->workspace()->put('three.json', json_encode([
            'three' => 3,
        ]));
        self::assertEquals([
            'one' => 'two',
            'three' => 3,
        ], $this->createLoader()->load($this->workspace()->path('test')));
    }

    public function testIncludeFiles(): void
    {
        $this->workspace()->put('test', json_encode([
            'one' => 'two',
            '$include' => [
                'three.json',
                'four.json',
            ],
        ]));
        $this->workspace()->put('three.json', json_encode([
            'three' => 3,
        ]));
        $this->workspace()->put('four.json', json_encode([
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

    private function createLoader(): ConfigLoader
    {
        return new ConfigLoader(new SeldLinter(), [new IncludeProcessor()]);
    }
}
