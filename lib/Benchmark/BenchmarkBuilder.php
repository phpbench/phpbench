<?php

namespace PhpBench\Benchmark;

use PhpBench\Benchmark\Subject;

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

        $subject = new Subject(
            $benchmark,
            $methodName,
            $subjectMeta['beforeMethod'],
            $subjectMeta['afterMethod'],
            $subjectMeta['paramProvider'],
            $subjectMeta['iterations'],
            $subjectMeta['revs'],
            $subjectMeta['group']
        );

        return $subject;
    }
}

