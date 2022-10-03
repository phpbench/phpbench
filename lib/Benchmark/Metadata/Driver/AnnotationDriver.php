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
    private $reader;
    private $subjectPattern;

    public function __construct($subjectPattern = '^bench', AnnotationReader $reader = null)
    {
        $this->reader = $reader ?: new AnnotationReader();
        $this->subjectPattern = $subjectPattern;
    }

    public function getMetadataForHierarchy(ReflectionHierarchy $hierarchy): BenchmarkMetadata
    {
        $primaryReflection = $hierarchy->getTop();
        $benchmark = new BenchmarkMetadata($primaryReflection->path, $primaryReflection->class);

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
                $hasPrefix = (bool) preg_match('{' . $this->subjectPattern . '}', $reflectionMethod->name);
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

    private function buildSubject(SubjectMetadata $subject, $annotations): void
    {
        foreach ($annotations as $annotation) {
            if ($annotation instanceof BeforeClassMethods) {
                throw new \InvalidArgumentException(sprintf(
                    '@BeforeClassMethods annotation can only be applied at the class level (%s)',
                    $subject->getBenchmark()->getClass() . '::' . $subject->getName()
                ));
            }

            if ($annotation instanceof AfterClassMethods) {
                throw new \InvalidArgumentException(sprintf(
                    '@AfterClassMethods annotation can only be applied at the class level (%s)',
                    $subject->getBenchmark()->getClass() . '::' . $subject->getName()
                ));
            }

            $this->processSubject($subject, $annotation);
        }
    }

    private function processSubject(SubjectMetadata $subject, $annotation): void
    {
        if ($annotation instanceof Annotations\BeforeMethods) {
            $subject->setBeforeMethods(
                $this->resolveValue(
                    $annotation,
                    $subject->getBeforeMethods(),
                    $annotation->getMethods()
                )
            );
        }

        if ($annotation instanceof Annotations\AfterMethods) {
            $subject->setAfterMethods(
                $this->resolveValue(
                    $annotation,
                    $subject->getAfterMethods(),
                    $annotation->getMethods()
                )
            );
        }

        if ($annotation instanceof Annotations\ParamProviders) {
            $subject->setParamProviders(
                $this->resolveValue(
                    $annotation,
                    $subject->getParamProviders(),
                    $annotation->getProviders()
                )
            );
        }

        if ($annotation instanceof Annotations\Iterations) {
            $subject->setIterations($annotation->getIterations());
        }

        if ($annotation instanceof Annotations\Sleep) {
            $subject->setSleep($annotation->getSleep());
        }

        if ($annotation instanceof Annotations\Groups) {
            $subject->setGroups(
                $this->resolveValue(
                    $annotation,
                    $subject->getGroups(),
                    $annotation->getGroups()
                )
            );
        }

        if ($annotation instanceof Annotations\Revs) {
            $subject->setRevs($annotation->getRevs());
        }

        if ($annotation instanceof Annotations\Warmup) {
            $subject->setWarmup($annotation->getRevs());
        }

        if ($annotation instanceof Annotations\Skip) {
            $subject->setSkip(true);
        }

        if ($annotation instanceof Annotations\OutputTimeUnit) {
            $subject->setOutputTimeUnit($annotation->getTimeUnit());
            $subject->setOutputTimePrecision($annotation->getPrecision());
        }

        if ($annotation instanceof Annotations\OutputMode) {
            $subject->setOutputMode($annotation->getMode());
        }

        if ($annotation instanceof Annotations\Assert) {
            $subject->addAssertion($annotation->getExpression());
        }

        if ($annotation instanceof Annotations\Format) {
            $subject->setFormat($annotation->getFormat());
        }

        if ($annotation instanceof Annotations\Executor) {
            $subject->setExecutor(new ExecutorMetadata($annotation->getName(), $annotation->getConfig()));
        }

        if ($annotation instanceof Annotations\Timeout) {
            $subject->setTimeout($annotation->getTimeout());
        }

        if ($annotation instanceof Annotations\RetryThreshold) {
            $subject->setRetryThreshold($annotation->getRetryThreshold());
        }
    }

    public function processBenchmark(BenchmarkMetadata $benchmark, $annotation): void
    {
        if ($annotation instanceof Annotations\Executor) {
            $benchmark->setExecutor(new ExecutorMetadata($annotation->getName(), $annotation->getConfig()));
        }

        if ($annotation instanceof BeforeClassMethods) {
            $benchmark->setBeforeClassMethods($annotation->getMethods());
        }

        if ($annotation instanceof AfterClassMethods) {
            $benchmark->setAfterClassMethods($annotation->getMethods());
        }
    }

    private function resolveValue(AbstractArrayAnnotation $annotation, ?array $currentValues, ?array $annotationValues): ?array
    {
        if ($currentValues === null) {
            return $annotationValues;
        }

        if ($annotationValues === null) {
            return $currentValues;
        }
        $values = $annotation->getExtend() === true ? $currentValues : [];
        $values = array_merge($values, $annotationValues);

        return $values;
    }
}
