<?php

namespace PhpBench\Tests\Unit\Benchmark\Metadata\Driver;

use PhpBench\Benchmark\Metadata\BenchmarkMetadata;
use PhpBench\Benchmark\Metadata\Driver\ChainDriver;
use PhpBench\Benchmark\Metadata\DriverInterface;
use PhpBench\Reflection\ReflectionClass;
use PhpBench\Reflection\ReflectionHierarchy;
use PhpBench\Tests\ProphecyTrait;
use PHPUnit\Framework\TestCase;

class ChainDriverTest extends TestCase
{
    use ProphecyTrait;

    public function testChain(): void
    {
        $driver1 = $this->prophesize(DriverInterface::class);
        $driver2 = $this->prophesize(DriverInterface::class);

        $benchmark1 = new BenchmarkMetadata(__FILE__, self::class);
        $benchmark1->setAfterClassMethods(['one']);
        $benchmark2 = new BenchmarkMetadata(__FILE__, self::class);
        $benchmark2->setAfterClassMethods(['two']);

        $hierarchy = new ReflectionHierarchy([
            new ReflectionClass(__FILE__, self::class)
        ]);

        $driver1->getMetadataForHierarchy($hierarchy)->willReturn($benchmark1);
        $driver2->getMetadataForHierarchy($hierarchy)->willReturn($benchmark2);

        $chain = new ChainDriver([
            $driver1->reveal(),
            $driver2->reveal(),
        ]);

        $metadata = $chain->getMetadataForHierarchy($hierarchy);

        self::assertEquals(['one', 'two'], $metadata->getAfterClassMethods());
    }
}
