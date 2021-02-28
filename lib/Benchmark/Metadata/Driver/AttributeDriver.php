<?php

namespace PhpBench\Benchmark\Metadata\Driver;

use PhpBench\Benchmark\Metadata\AnnotationReader;
use PhpBench\Benchmark\Metadata\Attributes;
use PhpBench\Benchmark\Metadata\Attributes\AbstractArrayAnnotation;
use PhpBench\Benchmark\Metadata\Attributes\AfterClassMethods;
use PhpBench\Benchmark\Metadata\Attributes\BeforeClassMethods;
use PhpBench\Benchmark\Metadata\Attributes\Subject;
use PhpBench\Benchmark\Metadata\BenchmarkMetadata;
use PhpBench\Benchmark\Metadata\DriverInterface;
use PhpBench\Benchmark\Metadata\ExecutorMetadata;
use PhpBench\Benchmark\Metadata\SubjectMetadata;
use PhpBench\Reflection\ReflectionHierarchy;
use PhpBench\Reflection\ReflectorInterface;

class AttributeDriver implements DriverInterface
{
    private $reflector;
    private $reader;
    private $subjectPattern;

    public function __construct(ReflectorInterface $reflector, $subjectPattern = '^bench', AnnotationReader $reader = null)
    {
        $this->reflector = $reflector;
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
            $benchAttributes = $this->reader->getClassAnnotations(
                $reflection
            );

            $annotations = array_merge($annotations, $benchAttributes);

            foreach ($annotations as $annotation) {
                $this->processBenchmark($benchmark, $annotation);
            }
        }

        foreach ($reflectionHierarchy as $reflection) {
            foreach ($reflection->methods as $reflectionMethod) {
                $hasPrefix = (bool) preg_match('{' . $this->subjectPattern . '}', $reflectionMethod->name);
                $hasAnnotation = false;
                $subjectAttributes = null;

                // if the prefix is false check to see if it has a `@Subject` annotation
                if (false === $hasPrefix) {
                    $subjectAttributes = $this->reader->getMethodAnnotations(
                        $reflectionMethod
                    );

                    foreach ($subjectAttributes as $annotation) {
                        if ($annotation instanceof Subject) {
                            $hasAnnotation = true;
                        }
                    }
                }

                if (false === $hasPrefix && false === $hasAnnotation) {
                    continue;
                }

                if (null === $subjectAttributes) {
                    $subjectAttributes = $this->reader->getMethodAnnotations(
                        $reflectionMethod
                    );
                }

                $subject = $benchmark->getOrCreateSubject($reflectionMethod->name);

                // apply the benchmark annotations to the subject
                foreach ($annotations as $annotation) {
                    $this->processSubject($subject, $annotation);
                }

                $this->buildSubject($subject, $subjectAttributes);
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
        if ($annotation instanceof Attributes\BeforeMethods) {
            $subject->setBeforeMethods(
                $this->resolveValue(
                    $annotation,
                    $subject->getBeforeMethods(),
                    $annotation->getMethods()
                )
            );
        }

        if ($annotation instanceof Attributes\AfterMethods) {
            $subject->setAfterMethods(
                $this->resolveValue(
                    $annotation,
                    $subject->getAfterMethods(),
                    $annotation->getMethods()
                )
            );
        }

        if ($annotation instanceof Attributes\ParamProviders) {
            $subject->setParamProviders(
                $this->resolveValue(
                    $annotation,
                    $subject->getParamProviders(),
                    $annotation->getProviders()
                )
            );
        }

        if ($annotation instanceof Attributes\Iterations) {
            $subject->setIterations($annotation->getIterations());
        }

        if ($annotation instanceof Attributes\Sleep) {
            $subject->setSleep($annotation->getSleep());
        }

        if ($annotation instanceof Attributes\Groups) {
            $subject->setGroups(
                $this->resolveValue(
                    $annotation,
                    $subject->getGroups(),
                    $annotation->getGroups()
                )
            );
        }

        if ($annotation instanceof Attributes\Revs) {
            $subject->setRevs($annotation->getRevs());
        }

        if ($annotation instanceof Attributes\Warmup) {
            $subject->setWarmup($annotation->getRevs());
        }

        if ($annotation instanceof Attributes\Skip) {
            $subject->setSkip(true);
        }

        if ($annotation instanceof Attributes\OutputTimeUnit) {
            $subject->setOutputTimeUnit($annotation->getTimeUnit());
            $subject->setOutputTimePrecision($annotation->getPrecision());
        }

        if ($annotation instanceof Attributes\OutputMode) {
            $subject->setOutputMode($annotation->getMode());
        }

        if ($annotation instanceof Attributes\Assert) {
            $subject->addAssertion($annotation->getExpression());
        }

        if ($annotation instanceof Attributes\Executor) {
            $subject->setExecutor(new ExecutorMetadata($annotation->getName(), $annotation->getConfig()));
        }

        if ($annotation instanceof Attributes\Timeout) {
            $subject->setTimeout($annotation->getTimeout());
        }
    }

    public function processBenchmark(BenchmarkMetadata $benchmark, $annotation): void
    {
        if ($annotation instanceof Attributes\Executor) {
            $benchmark->setExecutor(new ExecutorMetadata($annotation->getName(), $annotation->getConfig()));
        }

        if ($annotation instanceof BeforeClassMethods) {
            $benchmark->setBeforeClassMethods($annotation->getMethods());
        }

        if ($annotation instanceof AfterClassMethods) {
            $benchmark->setAfterClassMethods($annotation->getMethods());
        }
    }

    private function resolveValue(AbstractArrayAnnotation $annotation, array $currentValues, array $annotationValues): array
    {
        $values = $annotation->getExtend() === true ? $currentValues : [];
        $values = array_merge($values, $annotationValues);

        return $values;
    }
}

