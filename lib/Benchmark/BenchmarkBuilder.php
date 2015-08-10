<?php

namespace PhpBench\Benchmark;

use PhpBench\Benchmark\Subject;
use PhpBench\Benchmark\Teleflector;

class BenchmarkBuilder
{
    private $teleflector;
    private $parser;

    public function __construct(
        Teleflector $teleflector,
        Parser $parser
    )
    {
        $this->teleflector = $teleflector;
        $this->parser = $parser;
    }

    public function build($benchmarkPath, array $subjectFilter = array(), array $groupFilter = array())
    {
        $classInfo = $this->teleflector->getClassInfo($benchmarkPath);

        if (!in_array('PhpBench\BenchmarkInterface', $classInfo['interfaces'])) {
            return null;
        }

        $benchmark = new Benchmark(
            $benchmarkPath, 
            $classInfo['class']
        );

        $classMeta = $this->parser->parseDoc($classInfo['comment']);
        foreach ($classInfo['methods'] as $methodName => $methodInfo) {
            $subject = $this->buildSubject($benchmark, $methodName, $methodInfo, $classMeta, $subjectFilter, $groupFilter);

            if (null === $subject) {
                continue;
            }

            $this->validateSubject($classInfo, $subject);
            $benchmark->addSubject($subject);
        }

        return $benchmark;
    }

    private function buildSubject($benchmark, $methodName, $methodInfo, $classMeta, $subjectFilter, $groupFilter)
    {
        if (0 !== strpos($methodName, 'bench')) {
            return null;
        }

        // if we have a subject whitelist, only include subjects in that whitelistd
        if ($subjectFilter && false === in_array($methodName, $subjectFilter)) {
            return null;
        }

        $subjectMeta = $this->parser->parseDoc($methodInfo['comment'], $classMeta);

        if ($groupFilter && 0 === count(array_intersect($groupFilter, $subjectMeta['group']))) {
            return null;
        }

        if (empty($subjectMeta['revs'])) {
            $subjectMeta['revs'] = array(1);
        }

        $parameterSets = array();
        if ($subjectMeta['paramProvider']) {
            $parameterSets = $this->teleflector->getParameterSets($benchmark->getPath(), $subjectMeta['paramProvider']);
        }

        $subject = new Subject(
            $benchmark,
            $methodName,
            $subjectMeta['beforeMethod'],
            $subjectMeta['afterMethod'],
            $parameterSets,
            $subjectMeta['iterations'],
            $subjectMeta['revs'],
            $subjectMeta['group']
        );

        return $subject;
    }

    private function validateSubject($classInfo, $subject)
    {
        foreach ($subject->getBeforeMethods() as $beforeMethod) {
            if (!isset($classInfo['methods'][$beforeMethod])) {
                throw new \InvalidArgumentException(sprintf(
                    'Unknown before method "%s" in benchmark class "%s"',
                    $beforeMethod, $subject->getBenchmark()->getClassFqn()
                ));
            }
        }

        foreach ($subject->getAfterMethods() as $afterMethod) {
            if (!isset($classInfo['methods'][$afterMethod])) {
                throw new \InvalidArgumentException(sprintf(
                    'Unknown after method "%s" in benchmark class "%s"',
                    $afterMethod, $subject->getBenchmark()->getClassFqn()
                ));
            }
        }
    }
}
