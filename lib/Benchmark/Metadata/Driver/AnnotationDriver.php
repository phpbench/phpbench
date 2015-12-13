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
use PhpBench\Benchmark\Metadata\AbstractMetadata;
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
        $classMetadata = new BenchmarkMetadata($primaryReflection->path, $primaryReflection->class);

        $this->buildBenchmarkMetadata($classMetadata, $hierarchy);

        return $classMetadata;
    }

    private function buildBenchmarkMetadata(BenchmarkMetadata $classMetadata, ReflectionHierarchy $hierarchy)
    {
        $annotations = array();
        $reflectionHierarchy = array_reverse(iterator_to_array($hierarchy));

        foreach ($reflectionHierarchy as $reflection) {
            $benchAnnotations = $this->docParser->parse(
                $reflection->comment,
                sprintf('benchmark %s', $reflection->class)
            );
            $annotations = array_merge($annotations, $benchAnnotations);

            foreach ($benchAnnotations as $annotation) {
                if ($annotation instanceof BeforeClassMethods) {
                    $classMetadata->setBeforeClassMethods($annotation->getMethods());
                }
                if ($annotation instanceof AfterClassMethods) {
                    $classMetadata->setAfterClassMethods($annotation->getMethods());
                }

                $this->processAbstractMetadata($classMetadata, $annotation);
            }
        }

        foreach ($reflectionHierarchy as $reflection) {
            foreach ($reflection->methods as $reflectionMethod) {
                if ('bench' !== substr($reflectionMethod->name, 0, 5)) {
                    continue;
                }

                $subjectMetadata = $classMetadata->getOrCreateSubjectMetadata($reflectionMethod->name);

                // apply the benchmark annotations to the subject
                foreach ($annotations as $annotation) {
                    $this->processAbstractMetadata($subjectMetadata, $annotation);
                }

                $this->buildSubjectMetadata($subjectMetadata, $reflectionMethod);
                $classMetadata->setSubjectMetadata($subjectMetadata);
            }
        }
    }

    private function buildSubjectMetadata(SubjectMetadata $subjectMetadata, ReflectionMethod $reflectionMethod)
    {
        $annotations = $this->docParser->parse(
            $reflectionMethod->comment,
            sprintf('subject %s::%s', $reflectionMethod->class, $reflectionMethod->name
        ));

        foreach ($annotations as $annotation) {
            $this->processAbstractMetadata($subjectMetadata, $annotation);
        }
    }

    private function processAbstractMetadata(AbstractMetadata $metadata, $annotation)
    {
        if ($annotation instanceof Annotations\BeforeMethods) {
            $metadata->setBeforeMethods(
                $this->resolveValue(
                    $annotation,
                    $metadata->getBeforeMethods(),
                    $annotation->getMethods()
                )
            );
        }

        if ($annotation instanceof Annotations\AfterMethods) {
            $metadata->setAfterMethods(
                $this->resolveValue(
                    $annotation,
                    $metadata->getAfterMethods(),
                    $annotation->getMethods()
                )
            );
        }

        if ($annotation instanceof Annotations\ParamProviders) {
            $metadata->setParamProviders(
                $this->resolveValue(
                    $annotation,
                    $metadata->getParamProviders(),
                    $annotation->getProviders()
                )
            );
        }

        if ($annotation instanceof Annotations\Iterations) {
            $metadata->setIterations($annotation->getIterations());
        }

        if ($annotation instanceof Annotations\Sleep) {
            $metadata->setSleep($annotation->getSleep());
        }

        if ($annotation instanceof Annotations\Groups) {
            $metadata->setGroups(
                $this->resolveValue(
                    $annotation,
                    $metadata->getGroups(),
                    $annotation->getGroups()
                )
            );
        }

        if ($annotation instanceof Annotations\Revs) {
            $metadata->setRevs($annotation->getRevs());
        }

        if ($annotation instanceof Annotations\Skip) {
            $metadata->setSkip(true);
        }

        if ($annotation instanceof Annotations\OutputTimeUnit) {
            $metadata->setOutputTimeUnit($annotation->getOutputTimeUnit());
        }
    }

    private function resolveValue(AbstractArrayAnnotation $annotation, array $currentValues, array $annotationValues)
    {
        $values = $annotation->getExtend() === true ? $currentValues : array();
        $values = array_merge($values, $annotationValues);

        return $values;
    }
}
