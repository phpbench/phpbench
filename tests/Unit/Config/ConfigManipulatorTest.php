<?php

namespace PhpBench\Tests\Unit\Config;

use PHPUnit\Framework\TestCase;
use PhpBench\Config\ConfigManipulator;
use PhpBench\Tests\Util\Workspace;

class ConfigManipulatorTest extends TestCase
{
    private Workspace $workspace;

    protected function setUp(): void
    {
        $this->workspace = new Workspace(__DIR__ . '/../../Workspace');
        $this->workspace->reset();
    }

    public function testCreateNewConfig(): void
    {
        self::assertFileDoesNotExist($this->workspace->path('phpbench.json'));

        $schemaPath = 'path/to/json.schema';
        $this->createManipulator($schemaPath)->initialize();

        self::assertFileExists($this->workspace->path('phpbench.json'));
        $contents = $this->workspace->getContents('phpbench.json');
        self::assertJson($contents);
        $config = json_decode($contents, true, JSON_THROW_ON_ERROR);
        self::assertEquals($schemaPath, $config['$schema']);
    }

    public function testCreateNewConfigWithNullSchema(): void
    {
        self::assertFileDoesNotExist($this->workspace->path('phpbench.json'));

        $this->createManipulator(null)->initialize();

        self::assertFileExists($this->workspace->path('phpbench.json'));
        $contents = $this->workspace->getContents('phpbench.json');
        self::assertJson($contents);
        $config = json_decode($contents, true, JSON_THROW_ON_ERROR);
        self::assertEquals(['$schema' => null], $config);
    }

    public function testManipulateConfig(): void
    {
        $manipulator = $this->createManipulator();
        $manipulator->initialize();
        $manipulator->set('foo', ['bar' => 'baz']);
        $manipulator->set('bar', 12);
        $manipulator->delete('bar');

        $contents = $this->workspace->getContents('phpbench.json');
        $config = json_decode($contents, true, JSON_THROW_ON_ERROR);
        self::assertArrayHasKey('foo', $config);
        self::assertArrayNotHasKey('bar', $config);
    }

    public function testMergeToNonExisting(): void
    {
        $manipulator = $this->createManipulator();
        $manipulator->initialize();
        $manipulator->merge('foo', ['bar' => 'baz']);

        $contents = $this->workspace->getContents('phpbench.json');
        $config = json_decode($contents, true, JSON_THROW_ON_ERROR);
        self::assertArrayHasKey('foo', $config);
        self::assertEquals(['bar' => 'baz'], $config['foo']);
    }

    public function testMergeToExisting(): void
    {
        $manipulator = $this->createManipulator();
        $manipulator->initialize();
        $manipulator->set('foo', ['baz' => 'boo']);
        $manipulator->merge('foo', ['bar' => 'baz']);

        $contents = $this->workspace->getContents('phpbench.json');
        $config = json_decode($contents, true, JSON_THROW_ON_ERROR);
        self::assertArrayHasKey('foo', $config);
        self::assertEquals([
            'bar' => 'baz',
            'baz' => 'boo',
        ], $config['foo']);
    }

    private function createManipulator(?string $schemaPath = 'path/to/json.schema'): ConfigManipulator
    {
        return (new ConfigManipulator(
            $schemaPath,
            $this->workspace->path('phpbench.json')
        ));
    }
}
