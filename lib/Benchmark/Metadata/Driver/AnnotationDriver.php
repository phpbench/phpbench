<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Benchmark\Metadata\Driver;

use BetterReflection\Reflection\ReflectionClass;
use PhpBench\Benchmark\Metadata\AnnotationReader;
use PhpBench\Benchmark\Metadata\Annotations;
use PhpBench\Benchmark\Metadata\Annotations\AbstractArrayAnnotation;
use PhpBench\Benchmark\Metadata\Annotations\AfterClassMethods;
use PhpBench\Benchmark\Metadata\Annotations\BeforeClassMethods;
use PhpBench\Benchmark\Metadata\Annotations\Subject;
use PhpBench\Benchmark\Metadata\BenchmarkMetadata;
use PhpBench\Benchmark\Metadata\DriverInterface;
use PhpBench\Benchmark\Metadata\SubjectMetadata;

class AnnotationDriver implements DriverInterface
{
    private $reader;

    public function __construct(AnnotationReader $reader = null)
    {
        $this->reader = $reader ?: new AnnotationReader();
    }

    public function getMetadataForClass(ReflectionClass $class)
    {
        $benchmark = new BenchmarkMetadata($class->getFileName(), $class->getName());

        $this->buildBenchmark($benchmark, $class);

        return $benchmark;
    }

    private function buildBenchmark(BenchmarkMetadata $benchmark, ReflectionClass $class)
    {
        $annotations = [];
        $stack = [$class];

        while ($parent = $class->getParentClass()) {
            $stack[] = $parent;
            $class = $parent;
        }

        foreach (array_reverse($stack) as $class) {
            $benchAnnotations = $this->reader->getClassAnnotations(
                $class
            );

            $annotations = array_merge($annotations, $benchAnnotations);

            foreach ($annotations as $annotation) {
                $this->processBenchmark($benchmark, $annotation);
            }
        }

        foreach ($stack as $class) {
            foreach ($class->getMethods() as $method) {
                $hasPrefix = 'bench' === substr($method->getName(), 0, 5);
                $hasAnnotation = false;
                $subjectAnnotations = null;

                // if the prefix is false check to see if it has a `@Subject` annotation
                if (false === $hasPrefix) {
                    $subjectAnnotations = $this->reader->getMethodAnnotations(
                        $method
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
                        $method
                    );
                }

                $subject = $benchmark->getOrCreateSubject($method->getName());

                // apply the benchmark annotations to the subject
                foreach ($annotations as $annotation) {
                    $this->processSubject($subject, $annotation);
                }

                $this->buildSubject($subject, $subjectAnnotations);
            }
        }
    }

    private function buildSubject(SubjectMetadata $subject, $annotations)
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

    private function processSubject(SubjectMetadata $subject, $annotation)
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
    }

    public function processBenchmark(BenchmarkMetadata $benchmark, $annotation)
    {
        if ($annotation instanceof BeforeClassMethods) {
            $benchmark->setBeforeClassMethods($annotation->getMethods());
        }

        if ($annotation instanceof AfterClassMethods) {
            $benchmark->setAfterClassMethods($annotation->getMethods());
        }
    }

    private function resolveValue(AbstractArrayAnnotation $annotation, array $currentValues, array $annotationValues)
    {
        $values = $annotation->getExtend() === true ? $currentValues : [];
        $values = array_merge($values, $annotationValues);

        return $values;
    }
}
