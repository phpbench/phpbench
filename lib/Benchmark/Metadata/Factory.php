<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Benchmark\Metadata;

use PhpBench\Benchmark\Remote\ReflectionHierarchy;
use PhpBench\Benchmark\Remote\Reflector;

/**
 * Benchmark Metadata Factory.
 */
class Factory
{
    /**
     * @var Reflector
     */
    private $reflector;

    /**
     * @var DriverInterface
     */
    private $driver;

    /**
     * @param Reflector $reflector
     * @param DriverInterface $driver
     */
    public function __construct(Reflector $reflector, DriverInterface $driver)
    {
        $this->reflector = $reflector;
        $this->driver = $driver;
    }

    /**
     * Return a BenchmarkMetadata instance for the given file or NULL if the
     * given file contains no classes, or the class in the given file is
     * abstract.
     *
     * @param string $file
     *
     * @return BenchmarkMetadata
     */
    public function getMetadataForFile($file)
    {
        $hierarchy = $this->reflector->reflect($file);

        if ($hierarchy->isEmpty()) {
            return;
        }

        if ($hierarchy->getTop() && true === $hierarchy->getTop()->abstract) {
            return;
        }

        $metadata = $this->driver->getMetadataForHierarchy($hierarchy);

        // validate the subject and load the parameter sets
        foreach ($metadata->getSubjectMetadatas() as $subjectMetadata) {
            $this->validateSubject($hierarchy, $subjectMetadata);
            $paramProviders = $subjectMetadata->getParamProviders();
            $parameterSets = $this->reflector->getParameterSets($metadata->getPath(), $paramProviders);
            $subjectMetadata->setParameterSets($parameterSets);
        }

        return $metadata;
    }

    private function validateSubject(ReflectionHierarchy $benchmarkReflection, SubjectMetadata $subject)
    {
        foreach ($subject->getBeforeMethods() as $beforeMethod) {
            if (false === $benchmarkReflection->hasMethod($beforeMethod)) {
                throw new \InvalidArgumentException(sprintf(
                    'Unknown before method "%s" in benchmark class "%s"',
                    $beforeMethod, $subject->getClass()));
            }
        }

        foreach ($subject->getAfterMethods() as $afterMethod) {
            if (false === $benchmarkReflection->hasMethod($afterMethod)) {
                throw new \InvalidArgumentException(sprintf(
                    'Unknown after method "%s" in benchmark class "%s"',
                    $afterMethod, $subject->getClass()
                ));
            }
        }
    }
}
