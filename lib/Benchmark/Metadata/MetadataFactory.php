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
use PhpBench\Benchmark\Remote\Reflector;
use PhpBench\Model\Subject;

/**
 * Benchmark Metadata Factory.
 */
class MetadataFactory
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
     * Return a Benchmark instance for the given file or NULL if the
     * given file contains no classes, or the class in the given file is
     * abstract.
     */
    public function getMetadataForFile(string $file): ?BenchmarkMetadata
    {
        $hierarchy = $this->reflector->reflect($file);

        if ($hierarchy->isEmpty()) {
            return null;
        }

        try {
            $top = $hierarchy->getTop();
        } catch (\InvalidArgumentException $exception) {
            return null;
        }

        if (true === $top->abstract) {
            return null;
        }

        $metadata = $this->driver->getMetadataForHierarchy($hierarchy);
        $this->validateBenchmark($hierarchy, $metadata);

        // validate the subject and load the parameter sets
        foreach ($metadata->getSubjects() as $subject) {
            $this->validateSubject($hierarchy, $subject);
            $paramProviders = $subject->getParamProviders();
            $parameterSets = $this->reflector->getParameterSets($metadata->getPath(), $paramProviders);

            foreach ($parameterSets as $parameterSet) {
                if (!is_array($parameterSet)) {
                    throw new \InvalidArgumentException(sprintf(
                        'Each parameter set must be an array, got "%s" for %s::%s',
                        gettype($parameterSet),
                        $metadata->getClass(),
                        $subject->getName()
                    ));
                }
            }
            $subject->setParameterSets($parameterSets);
        }

        return $metadata;
    }

    private function validateSubject(ReflectionHierarchy $benchmarkReflection, SubjectMetadata $subject)
    {
        foreach (['getBeforeMethods' => 'before', 'getAfterMethods' => 'after'] as $methodName => $context) {
            foreach ($subject->$methodName() as $method) {
                $this->validateMethodExists($context, $benchmarkReflection, $method);
            }
        }
    }

    private function validateBenchmark(ReflectionHierarchy $hierarchy, BenchmarkMetadata $benchmark)
    {
        foreach (['getBeforeClassMethods' => 'before class', 'getAfterClassMethods' => 'after class'] as $methodName => $context) {
            foreach ($benchmark->$methodName() as $method) {
                $this->validateMethodExists($context, $hierarchy, $method, true);
            }
        }
    }

    private function validateMethodExists($context, ReflectionHierarchy $benchmarkReflection, $method, $isStatic = false)
    {
        if (false === $benchmarkReflection->hasMethod($method)) {
            throw new \InvalidArgumentException(sprintf(
                'Unknown %s method "%s" in benchmark class "%s"',
                $context, $method, $benchmarkReflection->getTop()->class
            ));
        }

        if ($isStatic !== $benchmarkReflection->hasStaticMethod($method)) {
            throw new \InvalidArgumentException(sprintf(
                '%s method "%s" must %s static in benchmark class "%s"',
                $context, $method,
                $isStatic ? 'be' : 'not be',
                $benchmarkReflection->getTop()->class
            ));
        }
    }
}
