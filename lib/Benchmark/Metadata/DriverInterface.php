<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace PhpBench\Benchmark\Metadata;

use PhpBench\Benchmark\Remote\ReflectionHierarchy;

/**
 * Interface for metadata drivers.
 */
interface DriverInterface
{
    /**
     * Return the metadata for the given class FQN.
     *
     * @param ReflectionHierarchy $classHierarchy
     *
     * @return BenchmarkMetadata
     */
    public function getMetadataForHierarchy(ReflectionHierarchy $classHierarchy): BenchmarkMetadata;
}
