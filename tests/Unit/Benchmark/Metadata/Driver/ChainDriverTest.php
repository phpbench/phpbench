<?php

namespace PhpBench\Tests\Unit\Benchmark\Metadata\Driver;

use Generator;
use PHPUnit\Framework\TestCase;
use PhpBench\Benchmark\Metadata\BenchmarkMetadata;
use PhpBench\Benchmark\Metadata\DriverInterface;
use PhpBench\Benchmark\Metadata\Driver\ChainDriver;
use PhpBench\Benchmark\Metadata\SubjectMetadata;
use PhpBench\Model\Benchmark;
use PhpBench\Reflection\ReflectionClass;
use PhpBench\Reflection\ReflectionHierarchy;
use PhpBench\Tests\ProphecyTrait;

class ChainDriverTest extends TestCase
{
    use ProphecyTrait;

    public function testChain(): void
    {
        $this->markTestIncomplete();
    }
}
