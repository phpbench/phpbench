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
        $this->validateAbstractMetadata($hierarchy, $metadata);

        foreach (array('getBeforeClassMethods' => 'before class', 'getAfterClassMethods' => 'after class') as $methodName => $context) {
            foreach ($metadata->$methodName() as $method) {
                $this->validateMethodExists($context, $hierarchy, $method, true);
            }
        }

        // validate the subject and load the parameter sets
        foreach ($metadata->getSubjectMetadatas() as $subjectMetadata) {
            $this->validateAbstractMetadata($hierarchy, $subjectMetadata);
            $paramProviders = $subjectMetadata->getParamProviders();
            $parameterSets = $this->reflector->getParameterSets($metadata->getPath(), $paramProviders);

            foreach ($parameterSets as $parameterSet) {
                if (!is_array($parameterSet)) {
                    throw new \InvalidArgumentException(sprintf(
                        'Each parameter set must be an array, got "%s" for %s::%s',
                        gettype($parameterSet),
                        $metadata->getClass(),
                        $subjectMetadata->getName()
                    ));
                }
            }
            $subjectMetadata->setParameterSets($parameterSets);
        }

        return $metadata;
    }

    private function validateAbstractMetadata(ReflectionHierarchy $benchmarkReflection, AbstractMetadata $subject)
    {
        foreach (array('getBeforeMethods' => 'before', 'getAfterMethods' => 'after') as $methodName => $context) {
            foreach ($subject->$methodName() as $method) {
                $this->validateMethodExists($context, $benchmarkReflection, $method);
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
                '%s method "%s" must be static in benchmark class "%s"',
                $context, $method, $benchmarkReflection->getTop()->class
            ));
        }
    }
}
