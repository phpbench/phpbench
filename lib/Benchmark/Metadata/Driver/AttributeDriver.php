<?php

namespace PhpBench\Benchmark\Metadata\Driver;

use PhpBench\Attributes;
use PhpBench\Attributes\AfterClassMethods;
use PhpBench\Attributes\BeforeClassMethods;
use PhpBench\Attributes\Subject;
use PhpBench\Benchmark\Metadata\BenchmarkMetadata;
use PhpBench\Benchmark\Metadata\DriverInterface;
use PhpBench\Benchmark\Metadata\ExecutorMetadata;
use PhpBench\Benchmark\Metadata\SubjectMetadata;
use PhpBench\Reflection\ReflectionClass;
use PhpBench\Reflection\ReflectionHierarchy;
use PhpBench\Reflection\ReflectionMethod;

use function array_key_exists;

class AttributeDriver implements DriverInterface
{
    /**
     * @var string
     */
    private $subjectPattern;

    public function __construct(string $subjectPattern = '^bench')
    {
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
        $attributes = [];
        $reflectionHierarchy = iterator_to_array($hierarchy);

        foreach ($reflectionHierarchy as $reflection) {
            assert($reflection instanceof ReflectionClass);
            $benchAttributes = $reflection->attributes;

            $attributes = array_merge($attributes, $benchAttributes);

            foreach ($attributes as $attribute) {
                $this->processBenchmark($benchmark, $attribute);
            }
        }

        foreach ($reflectionHierarchy as $reflection) {
            foreach ($reflection->methods as $reflectionMethod) {
                assert($reflectionMethod instanceof ReflectionMethod);
                $hasPrefix = (bool) preg_match('{' . $this->subjectPattern . '}', $reflectionMethod->name);
                $hasAttribute = false;
                $subjectAttributes = null;

                // if the prefix is false check to see if it has a `@Subject` attribute
                if (false === $hasPrefix) {
                    $subjectAttributes = $reflectionMethod->attributes;

                    foreach ($subjectAttributes as $attribute) {
                        if ($attribute instanceof Subject) {
                            $hasAttribute = true;
                        }
                    }
                }

                if (false === $hasPrefix && false === $hasAttribute) {
                    continue;
                }

                if (null === $subjectAttributes) {
                    $subjectAttributes = $reflectionMethod->attributes;
                }

                $subjects = $benchmark->getSubjects();

                if (array_key_exists($reflectionMethod->name, $subjects)) {
                    // A subject with the same name has already been processed: skip parent class subjects
                    // with the same name.
                    continue;
                }

                $subject = $benchmark->getOrCreateSubject($reflectionMethod->name);

                // apply the benchmark attributes to the subject
                foreach ($attributes as $attribute) {
                    $this->processSubject($subject, $attribute);
                }

                $this->buildSubject($subject, $subjectAttributes);
            }
        }
    }

    /**
     * @param object[] $attributes
     */
    private function buildSubject(SubjectMetadata $subject, $attributes): void
    {
        foreach ($attributes as $attribute) {
            if ($attribute instanceof BeforeClassMethods) {
                throw new \InvalidArgumentException(sprintf(
                    '@BeforeClassMethods attribute can only be applied at the class level (%s)',
                    $subject->getBenchmark()->getClass() . '::' . $subject->getName()
                ));
            }

            if ($attribute instanceof AfterClassMethods) {
                throw new \InvalidArgumentException(sprintf(
                    '@AfterClassMethods attribute can only be applied at the class level (%s)',
                    $subject->getBenchmark()->getClass() . '::' . $subject->getName()
                ));
            }

            $this->processSubject($subject, $attribute);
        }
    }

    /**
     * @param object $attribute
     */
    private function processSubject(SubjectMetadata $subject, $attribute): void
    {
        if ($attribute instanceof Attributes\BeforeMethods) {
            $subject->setBeforeMethods(array_merge(
                $subject->getBeforeMethods(),
                $attribute->methods
            ));
        }

        if ($attribute instanceof Attributes\AfterMethods) {
            $subject->setAfterMethods(array_merge(
                $subject->getAfterMethods(),
                $attribute->methods
            ));
        }

        if ($attribute instanceof Attributes\ParamProviders) {
            $subject->setParamProviders(array_merge(
                $subject->getParamProviders(),
                $attribute->providers
            ));
        }

        if ($attribute instanceof Attributes\Iterations) {
            $subject->setIterations($attribute->iterations);
        }

        if ($attribute instanceof Attributes\Sleep) {
            $subject->setSleep($attribute->sleep);
        }

        if ($attribute instanceof Attributes\Groups) {
            $subject->setGroups(array_merge(
                $subject->getGroups(),
                $attribute->groups
            ));
        }

        if ($attribute instanceof Attributes\Revs) {
            $subject->setRevs($attribute->revs);
        }

        if ($attribute instanceof Attributes\Warmup) {
            $subject->setWarmup($attribute->revs);
        }

        if ($attribute instanceof Attributes\Skip) {
            $subject->setSkip(true);
        }

        if ($attribute instanceof Attributes\OutputTimeUnit) {
            $subject->setOutputTimeUnit($attribute->timeUnit);
            $subject->setOutputTimePrecision($attribute->precision);
        }

        if ($attribute instanceof Attributes\OutputMode) {
            $subject->setOutputMode($attribute->getMode());
        }

        if ($attribute instanceof Attributes\Assert) {
            $subject->addAssertion($attribute->expression);
        }

        if ($attribute instanceof Attributes\Format) {
            $subject->setFormat($attribute->format);
        }

        if ($attribute instanceof Attributes\Executor) {
            $subject->setExecutor(new ExecutorMetadata($attribute->name, $attribute->config));
        }

        if ($attribute instanceof Attributes\Timeout) {
            $subject->setTimeout($attribute->timeout);
        }

        if ($attribute instanceof Attributes\RetryThreshold) {
            $subject->setRetryThreshold($attribute->retryThreshold);
        }
    }

    /**
     * @param object $attribute
     */
    public function processBenchmark(BenchmarkMetadata $benchmark, $attribute): void
    {
        if ($attribute instanceof Attributes\Executor) {
            $benchmark->setExecutor(new ExecutorMetadata($attribute->name, $attribute->config));
        }

        if ($attribute instanceof BeforeClassMethods) {
            $benchmark->setBeforeClassMethods($attribute->methods);
        }

        if ($attribute instanceof AfterClassMethods) {
            $benchmark->setAfterClassMethods($attribute->methods);
        }
    }
}
