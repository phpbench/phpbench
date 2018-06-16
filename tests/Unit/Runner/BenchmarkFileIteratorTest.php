<?php

namespace PhpBench\Tests\Unit\Runner;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use PhpBench\Runner\BenchmarkFileIterator;
use function iterator_to_array;

class BenchmarkFileIteratorTest extends TestCase
{
    public function testThrowsExceptionIfPathDoesNotExist()
    {
        $this->expectException(InvalidArgumentException::class);
        $iterator = $this->create(__DIR__ . '/not-existing-file.php');
        iterator_to_array($iterator);
    }

    private function create(string $path)
    {
        return new BenchmarkFileIterator($path);
    }
}
