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
use PhpBench\Benchmark\Metadata\BenchmarkMetadata;
use PhpBench\Benchmark\Metadata\DocParser;
use PhpBench\Benchmark\Metadata\DriverInterface;
use PhpBench\Benchmark\Metadata\SubjectMetadata;
use PhpBench\Benchmark\Remote\ReflectionClass;
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

        foreach (array_reverse(iterator_to_array($hierarchy)) as $reflection) {
            $this->buildBenchmarkMetadata($classMetadata, $reflection);
        }

        return $classMetadata;
    }

    private function buildBenchmarkMetadata(BenchmarkMetadata $classMetadata, ReflectionClass $reflection)
    {
        $annotations = $this->docParser->parse(
            $reflection->comment,
            sprintf('benchmark %s', $reflection->class)
        );

        foreach ($annotations as $annotation) {
            $this->processAbstractMetadata($classMetadata, $annotation);
        }

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
            $metadata->setBeforeMethods($annotation->getMethods());
        }

        if ($annotation instanceof Annotations\AfterMethods) {
            $metadata->setAfterMethods($annotation->getMethods());
        }

        if ($annotation instanceof Annotations\ParamProviders) {
            $metadata->setParamProviders($annotation->getProviders());
        }

        if ($annotation instanceof Annotations\Iterations) {
            $metadata->setIterations($annotation->getIterations());
        }

        if ($annotation instanceof Annotations\Groups) {
            $metadata->setGroups($annotation->getGroups());
        }

        if ($annotation instanceof Annotations\Revs) {
            $metadata->setRevs($annotation->getRevs());
        }

        if ($annotation instanceof Annotations\Skip) {
            $metadata->setSkip(true);
        }
    }
}
