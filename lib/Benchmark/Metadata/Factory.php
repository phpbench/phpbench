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
use PhpBench\Model\Subject;
use BetterReflection\Reflector\ClassReflector;
use BetterReflection\Reflection\ReflectionClass;
use PhpBench\Reflection\FileReflectorInterface;

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
    public function __construct(FileReflectorInterface $reflector, DriverInterface $driver)
    {
        $this->reflector = $reflector;
        $this->driver = $driver;
    }

    /**
     * Return a Benchmark instance for the given file or NULL if the
     * given file contains no classes, or the class in the given file is
     * abstract.
     *
     * @param string $file
     *
     * @return Benchmark
     */
    public function getMetadataForFile($file)
    {
        $class = $this->reflector->reflectFile($file);

        if (null === $class) {
            return;
        }

        if ($class->isAbstract()) {
            return;
        }

        $metadata = $this->driver->getMetadataForClass($class);
        $this->validateBenchmark($class, $metadata);

        // validate the subject and load the parameter sets
        foreach ($metadata->getSubjects() as $subject) {
            $this->validateSubject($class, $subject);
            $paramProviders = $subject->getParamProviders();
            $parameterSets = $this->getParameterSets($paramProviders, $class);

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

    private function validateSubject(ReflectionClass $class, SubjectMetadata $subject)
    {
        foreach (['getBeforeMethods' => 'before', 'getAfterMethods' => 'after'] as $methodName => $context) {
            foreach ($subject->$methodName() as $method) {
                $this->validateMethodExists($context, $class, $method);
            }
        }
    }

    private function validateBenchmark(ReflectionClass $class, BenchmarkMetadata $benchmark)
    {
        foreach (['getBeforeClassMethods' => 'before class', 'getAfterClassMethods' => 'after class'] as $methodName => $context) {
            foreach ($benchmark->$methodName() as $method) {
                $this->validateMethodExists($context, $class, $method, true);
            }
        }
    }

    private function validateMethodExists($context, ReflectionClass $class, $method, $isStatic = false)
    {
        if (false === $class->hasMethod($method)) {
            throw new \InvalidArgumentException(sprintf(
                'Unknown %s method "%s" in benchmark class "%s"',
                $context, $method, $class->getName()
            ));
        }

        if ($isStatic !== $class->getMethod($method)->isStatic()) {
            throw new \InvalidArgumentException(sprintf(
                '%s method "%s" must %s static in benchmark class "%s"',
                $context, $method,
                $isStatic ? 'be' : 'not be',
                $class->getName()
            ));
        }
    }

    private function getParameterSets(array $paramProviders, ReflectionClass $class)
    {
        $parameterSets = [];
        foreach ($paramProviders as $paramProvider) {
            $method = $class->getMethod($paramProvider);
            $parameterSets[] = eval($method->getBodyCode());
        }

        return $parameterSets;
    }
}
