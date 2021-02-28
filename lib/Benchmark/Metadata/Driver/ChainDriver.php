<?php

namespace PhpBench\Benchmark\Metadata\Driver;

use PhpBench\Benchmark\Metadata\BenchmarkMetadata;
use PhpBench\Benchmark\Metadata\DriverInterface;
use PhpBench\Reflection\ReflectionHierarchy;

class ChainDriver implements DriverInterface
{
    /**
     * @var DriverInterface[]
     */
    private $drivers;

    /**
     * @param DriverInterface[] $drivers
     */
    public function __construct(array $drivers)
    {
        $this->drivers = $drivers;
    }

    /**
     * {@inheritDoc}
     */
    public function getMetadataForHierarchy(ReflectionHierarchy $classHierarchy): BenchmarkMetadata
    {
        $primaryReflection = $classHierarchy->getTop();
        $benchmark = new BenchmarkMetadata($primaryReflection->path, $primaryReflection->class);

        foreach ($this->drivers as $driver) {
            $benchmark->merge($driver->getMetadataForHierarchy($classHierarchy));
        }

        return $benchmark;
    }
}
