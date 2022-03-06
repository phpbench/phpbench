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

use InvalidArgumentException;
use PhpBench\Model\Exception\InvalidParameterSets;
use PhpBench\Model\Subject;
use PhpBench\Reflection\ReflectionHierarchy;
use PhpBench\Reflection\ReflectorInterface;
use PhpBench\Benchmark\Metadata\Exception\CouldNotLoadMetadataException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Benchmark Metadata Factory.
 */
class MetadataFactory
{
    /**
     * @var ReflectorInterface
     */
    private $reflector;

    /**
     * @var DriverInterface
     */
    private $driver;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var bool
     */
    private $warnOnMetadataError = false;

    public function __construct(ReflectorInterface $reflector, DriverInterface $driver, LoggerInterface $logger = null, bool $warnOnMetadataError = false)
    {
        $this->reflector = $reflector;
        $this->driver = $driver;
        $this->logger = $logger ?: new NullLogger();
        $this->warnOnMetadataError = $warnOnMetadataError;
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

        try {
            $metadata = $this->driver->getMetadataForHierarchy($hierarchy);
        } catch (CouldNotLoadMetadataException $couldNotLoad) {
            if (false === $this->warnOnMetadataError) {
                throw $couldNotLoad;
            }
            $this->logger->warning(sprintf(
                'Could not load metadata for file "%s" - is this file intended to be a benchmark? Perhaps setting the `runner.file_pattern` to `*Bench.php` will help: %s',
                $file,
                $couldNotLoad->getMessage()
            ));

            return null;
        }
        $this->validateBenchmark($hierarchy, $metadata);

        // validate the subject and load the parameter sets
        foreach ($metadata->getSubjects() as $subject) {
            $this->validateSubject($hierarchy, $subject);
            $paramProviders = $subject->getParamProviders();

            if (!$paramProviders) {
                continue;
            }

            try {
                $parameterSets = $this->reflector->getParameterSets($metadata->getPath(), $paramProviders);
            } catch (InvalidParameterSets $invalid) {
                throw new InvalidArgumentException(sprintf(
                    '%s for %s::%s',
                    $invalid->getMessage(),
                    $metadata->getClass(),
                    $subject->getName()
                ));
            }
            $subject->setParameterSets($parameterSets);
        }

        return $metadata;
    }

    private function validateSubject(ReflectionHierarchy $benchmarkReflection, SubjectMetadata $subject): void
    {
        foreach (['getBeforeMethods' => 'before', 'getAfterMethods' => 'after'] as $methodName => $context) {
            foreach ($subject->$methodName() as $method) {
                $this->validateMethodExists($context, $benchmarkReflection, $method);
            }
        }
    }

    private function validateBenchmark(ReflectionHierarchy $hierarchy, BenchmarkMetadata $benchmark): void
    {
        foreach (['getBeforeClassMethods' => 'before class', 'getAfterClassMethods' => 'after class'] as $methodName => $context) {
            foreach ($benchmark->$methodName() as $method) {
                $this->validateMethodExists($context, $hierarchy, $method, true);
            }
        }
    }

    private function validateMethodExists($context, ReflectionHierarchy $benchmarkReflection, $method, $isStatic = false): void
    {
        if (false === $benchmarkReflection->hasMethod($method)) {
            throw new \InvalidArgumentException(sprintf(
                'Unknown %s method "%s" in benchmark class "%s"',
                $context,
                $method,
                $benchmarkReflection->getTop()->class
            ));
        }

        if ($isStatic !== $benchmarkReflection->hasStaticMethod($method)) {
            throw new \InvalidArgumentException(sprintf(
                '%s method "%s" must %s static in benchmark class "%s"',
                $context,
                $method,
                $isStatic ? 'be' : 'not be',
                $benchmarkReflection->getTop()->class
            ));
        }
    }
}
