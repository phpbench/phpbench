<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Benchmark;

class BenchmarkBuilder
{
    private $teleflector;
    private $parser;

    public function __construct(
        Teleflector $teleflector,
        Parser $parser
    ) {
        $this->teleflector = $teleflector;
        $this->parser = $parser;
    }

    public function build($benchmarkPath, array $subjectFilter = array(), array $groupFilter = array())
    {
        // we cannot instantiate the class as it may have non-existing classes.
        // (benchmarks have their own autoloading environment and are executed
        // in separate processes).
        $classHierarchy = $this->teleflector->getClassInfo($benchmarkPath);
        $classInfo = reset($classHierarchy);

        if (true === $classInfo['abstract']) {
            return;
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
            return;
        }

        // if we have a subject whitelist, only include subjects in that whitelistd
        if ($subjectFilter && false === in_array($methodName, $subjectFilter)) {
            return;
        }

        $subjectMeta = $this->parser->parseDoc($methodInfo['comment'], $classMeta);

        if ($groupFilter && 0 === count(array_intersect($groupFilter, $subjectMeta['group']))) {
            return;
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
