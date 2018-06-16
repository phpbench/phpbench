<?php

namespace PhpBench\Tests\Unit\Runner;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use PhpBench\Runner\BenchmarkFileIterator;
use PhpBench\Tests\Util\Workspace;
use SplFileInfo;
use function iterator_to_array;

class BenchmarkFileIteratorTest extends TestCase
{
    public function testThrowsExceptionIfPathDoesNotExist()
    {
        $this->expectException(InvalidArgumentException::class);
        $iterator = $this->create(__DIR__ . '/not-existing-file.php');
        iterator_to_array($iterator);
    }

    public function testIteratesOverFilesInDirectory()
    {
        $workspace = Workspace::create()->init();
        $workspace->createFile('foobar/hello.php');
        $workspace->createFile('foobar/barfoo/hello.php');
        $workspace->createFile('foobar/goodbye.php');
        $workspace->createFile('goodbye.php');

        $iterator = $this->create($workspace->path('/foobar'));
        $files = array_map(function (SplFileInfo $file) {
            return $file->getPathname();
        }, iterator_to_array($iterator));

        $this->assertEquals([
            $workspace->path('foobar/hello.php'),
            $workspace->path('foobar/barfoo/hello.php'),
            $workspace->path('foobar/goodbye.php'),
        ], $files);
    }

    public function testReturnsSingleFileIfAbsolutePathProvided()
    {
        $workspace = Workspace::create()->init();
        $workspace->createFile('hello.php');
        $workspace->createFile('foobar/barfoo/hello.php');
        $workspace->createFile('foobar/goodbye.php');
        $workspace->createFile('goodbye.php');

        $iterator = $this->create($workspace->path('hello.php'));
        $files = array_map(function (SplFileInfo $file) {
            return $file->getPathname();
        }, iterator_to_array($iterator));

        $this->assertCount(1, $files);
        $this->assertEquals([
            $workspace->path('hello.php'),
        ], $files);
    }

    private function create(string $path)
    {
        return new BenchmarkFileIterator($path);
    }
}
