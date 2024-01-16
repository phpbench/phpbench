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

namespace PhpBench\Benchmark\Metadata\Driver;

use InvalidArgumentException;
use PhpBench\Benchmark\Metadata\Annotations\BeforeMethods;
use PhpBench\Benchmark\Metadata\Annotations\AfterMethods;
use PhpBench\Benchmark\Metadata\Annotations\ParamProviders;
use PhpBench\Benchmark\Metadata\Annotations\Iterations;
use PhpBench\Benchmark\Metadata\Annotations\Sleep;
use PhpBench\Benchmark\Metadata\Annotations\Groups;
use PhpBench\Benchmark\Metadata\Annotations\Revs;
use PhpBench\Benchmark\Metadata\Annotations\Warmup;
use PhpBench\Benchmark\Metadata\Annotations\Skip;
use PhpBench\Benchmark\Metadata\Annotations\OutputTimeUnit;
use PhpBench\Benchmark\Metadata\Annotations\OutputMode;
use PhpBench\Benchmark\Metadata\Annotations\Assert;
use PhpBench\Benchmark\Metadata\Annotations\Format;
use PhpBench\Benchmark\Metadata\Annotations\Executor;
use PhpBench\Benchmark\Metadata\Annotations\Timeout;
use PhpBench\Benchmark\Metadata\Annotations\RetryThreshold;
use PhpBench\Benchmark\Metadata\AnnotationReader;
use PhpBench\Benchmark\Metadata\Annotations;
use PhpBench\Benchmark\Metadata\Annotations\AbstractArrayAnnotation;
use PhpBench\Benchmark\Metadata\Annotations\AfterClassMethods;
use PhpBench\Benchmark\Metadata\Annotations\BeforeClassMethods;
use PhpBench\Benchmark\Metadata\Annotations\Subject;
use PhpBench\Benchmark\Metadata\BenchmarkMetadata;
use PhpBench\Benchmark\Metadata\DriverInterface;
use PhpBench\Benchmark\Metadata\ExecutorMetadata;
use PhpBench\Benchmark\Metadata\SubjectMetadata;
use PhpBench\Reflection\ReflectionHierarchy;

class AnnotationDriver implements DriverInterface
{
    private readonly AnnotationReader $reader;

    /**
     * @param string $subjectPattern
     */
    public function __construct(private $subjectPattern = '^bench', AnnotationReader $reader = null)
    {
        $this->reader = $reader ?: new AnnotationReader();
    }

    public function getMetadataForHierarchy(ReflectionHierarchy $hierarchy): BenchmarkMetadata
    {
        $primaryReflection = $hierarchy->getTop();
        $benchmark = new BenchmarkMetadata($primaryReflection->path, $primaryReflection->getClass());

        $this->buildBenchmark($benchmark, $hierarchy);

        return $benchmark;
    }

    private function buildBenchmark(BenchmarkMetadata $benchmark, ReflectionHierarchy $hierarchy): void
    {
        $annotations = [];
        $reflectionHierarchy = array_reverse(iterator_to_array($hierarchy));

        foreach ($reflectionHierarchy as $reflection) {
            $benchAnnotations = $this->reader->getClassAnnotations(
                $reflection
            );

            $annotations = array_merge($annotations, $benchAnnotations);

            foreach ($annotations as $annotation) {
                $this->processBenchmark($benchmark, $annotation);
            }
        }

        foreach ($reflectionHierarchy as $reflection) {
            foreach ($reflection->methods as $reflectionMethod) {
                $hasPrefix = (bool) preg_match('{' . $this->subjectPattern . '}', (string) $reflectionMethod->name);
                $hasAnnotation = false;
                $subjectAnnotations = null;

                // if the prefix is false check to see if it has a `@Subject` annotation
                if (false === $hasPrefix) {
                    $subjectAnnotations = $this->reader->getMethodAnnotations(
                        $reflectionMethod
                    );

                    foreach ($subjectAnnotations as $annotation) {
                        if ($annotation instanceof Subject) {
                            $hasAnnotation = true;
                        }
                    }
                }

                if (false === $hasPrefix && false === $hasAnnotation) {
                    continue;
                }

                if (null === $subjectAnnotations) {
                    $subjectAnnotations = $this->reader->getMethodAnnotations(
                        $reflectionMethod
                    );
                }

                $subject = $benchmark->getOrCreateSubject($reflectionMethod->name);

                // apply the benchmark annotations to the subject
                foreach ($annotations as $annotation) {
                    $this->processSubject($subject, $annotation);
                }

                $this->buildSubject($subject, $subjectAnnotations);
            }
        }
    }

    /**
     * @param object[] $annotations
     */
    private function buildSubject(SubjectMetadata $subject, array $annotations): void
    {
        foreach ($annotations as $annotation) {
            if ($annotation instanceof BeforeClassMethods) {
                throw new InvalidArgumentException(sprintf(
                    '@BeforeClassMethods annotation can only be applied at the class level (%s)',
                    $subject->getBenchmark()->getClass() . '::' . $subject->getName()
                ));
            }

            if ($annotation instanceof AfterClassMethods) {
                throw new InvalidArgumentException(sprintf(
                    '@AfterClassMethods annotation can only be applied at the class level (%s)',
                    $subject->getBenchmark()->getClass() . '::' . $subject->getName()
                ));
            }

            $this->processSubject($subject, $annotation);
        }
    }

    private function processSubject(SubjectMetadata $subject, object $annotation): void
    {
        if ($annotation instanceof BeforeMethods) {
            $subject->setBeforeMethods(
                $this->resolveValue(
                    $annotation,
                    $subject->getBeforeMethods(),
                    $annotation->getMethods()
                )
            );
        }

        if ($annotation instanceof AfterMethods) {
            $subject->setAfterMethods(
                $this->resolveValue(
                    $annotation,
                    $subject->getAfterMethods(),
                    $annotation->getMethods()
                )
            );
        }

        if ($annotation instanceof ParamProviders) {
            $subject->setParamProviders(
                $this->resolveValue(
                    $annotation,
                    $subject->getParamProviders(),
                    $annotation->getProviders()
                )
            );
        }

        if ($annotation instanceof Iterations) {
            $subject->setIterations($annotation->getIterations());
        }

        if ($annotation instanceof Sleep) {
            $subject->setSleep($annotation->getSleep());
        }

        if ($annotation instanceof Groups) {
            $subject->setGroups(
                $this->resolveValue(
                    $annotation,
                    $subject->getGroups(),
                    $annotation->getGroups()
                )
            );
        }

        if ($annotation instanceof Revs) {
            $subject->setRevs($annotation->getRevs());
        }

        if ($annotation instanceof Warmup) {
            $subject->setWarmup($annotation->getRevs());
        }

        if ($annotation instanceof Skip) {
            $subject->setSkip(true);
        }

        if ($annotation instanceof OutputTimeUnit) {
            $subject->setOutputTimeUnit($annotation->getTimeUnit());
            $subject->setOutputTimePrecision($annotation->getPrecision());
        }

        if ($annotation instanceof OutputMode) {
            $subject->setOutputMode($annotation->getMode());
        }

        if ($annotation instanceof Assert) {
            $subject->addAssertion($annotation->getExpression());
        }

        if ($annotation instanceof Format) {
            $subject->setFormat($annotation->getFormat());
        }

        if ($annotation instanceof Executor) {
            $subject->setExecutor(new ExecutorMetadata($annotation->getName(), $annotation->getConfig()));
        }

        if ($annotation instanceof Timeout) {
            $subject->setTimeout($annotation->getTimeout());
        }

        if ($annotation instanceof RetryThreshold) {
            $subject->setRetryThreshold($annotation->getRetryThreshold());
        }
    }

    public function processBenchmark(BenchmarkMetadata $benchmark, object $annotation): void
    {
        if ($annotation instanceof Executor) {
            $benchmark->setExecutor(new ExecutorMetadata($annotation->getName(), $annotation->getConfig()));
        }

        if ($annotation instanceof BeforeClassMethods) {
            $benchmark->setBeforeClassMethods($annotation->getMethods());
        }

        if ($annotation instanceof AfterClassMethods) {
            $benchmark->setAfterClassMethods($annotation->getMethods());
        }
    }

    /**
     * @param string[] $currentValues
     * @param string[] $annotationValues
     *
     * @return string[]
     */
    private function resolveValue(AbstractArrayAnnotation $annotation, array $currentValues, array $annotationValues): array
    {
        $values = $annotation->getExtend() === true ? $currentValues : [];
        $values = array_merge($values, $annotationValues);

        return $values;
    }
}
