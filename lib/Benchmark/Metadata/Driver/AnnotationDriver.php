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

use Doctrine\Common\Annotations\AnnotationRegistry;
use PhpBench\Benchmark\Metadata\Annotations;
use PhpBench\Benchmark\Metadata\Annotations\AbstractArrayAnnotation;
use PhpBench\Benchmark\Metadata\Annotations\AfterClassMethods;
use PhpBench\Benchmark\Metadata\Annotations\BeforeClassMethods;
use PhpBench\Benchmark\Metadata\BenchmarkMetadata;
use PhpBench\Benchmark\Metadata\DocParser;
use PhpBench\Benchmark\Metadata\DriverInterface;
use PhpBench\Benchmark\Metadata\SubjectMetadata;
use PhpBench\Benchmark\Remote\ReflectionHierarchy;
use PhpBench\Benchmark\Remote\ReflectionMethod;
use PhpBench\Benchmark\Remote\Reflector;

class AnnotationDriver implements DriverInterface
{
    private $reflector;
    private $docParser;

    public function __construct(Reflector $reflector)
    {
        AnnotationRegistry::registerLoader(function ($classFqn) {
            if (class_exists($classFqn)) {
                return true;
            }
        });
        $this->reflector = $reflector;

        // doc parser is final, can't mock it, no point in injecting it.
        $this->docParser = new DocParser();
    }

    public function getMetadataForHierarchy(ReflectionHierarchy $hierarchy)
    {
        $primaryReflection = $hierarchy->getTop();
        $benchmark = new BenchmarkMetadata($primaryReflection->path, $primaryReflection->class);

        $this->buildBenchmark($benchmark, $hierarchy);

        return $benchmark;
    }

    private function buildBenchmark(BenchmarkMetadata $benchmark, ReflectionHierarchy $hierarchy)
    {
        $annotations = [];
        $reflectionHierarchy = array_reverse(iterator_to_array($hierarchy));

        foreach ($reflectionHierarchy as $reflection) {
            $benchAnnotations = $this->docParser->parse(
                $reflection->comment,
                sprintf('benchmark %s', $reflection->class)
            );

            $annotations = array_merge($annotations, $benchAnnotations);

            foreach ($annotations as $annotation) {
                $this->processBenchmark($benchmark, $annotation);
            }
        }

        foreach ($reflectionHierarchy as $reflection) {
            foreach ($reflection->methods as $reflectionMethod) {
                if ('bench' !== substr($reflectionMethod->name, 0, 5)) {
                    continue;
                }

                $subject = $benchmark->getOrCreateSubject($reflectionMethod->name);

                // apply the benchmark annotations to the subject
                foreach ($annotations as $annotation) {
                    $this->processSubject($subject, $annotation);
                }

                $this->buildSubject($subject, $reflectionMethod);
            }
        }
    }

    private function buildSubject(SubjectMetadata $subject, ReflectionMethod $reflectionMethod)
    {
        $annotations = $this->docParser->parse(
            $reflectionMethod->comment,
            sprintf('subject %s::%s', $reflectionMethod->class, $reflectionMethod->name
        ));

        foreach ($annotations as $annotation) {
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
