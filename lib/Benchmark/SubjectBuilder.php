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

use PhpBench\Benchmark;
use PhpBench\Exception\InvalidArgumentException;

class SubjectBuilder
{
    /**
     * @var integer
     */
    static $subjectIdCounter = 0;

    /**
     * @var Parser
     */
    private $parser;

    /**
     * @param array $subjects Subject whitelist (empty implies all subjects)
     * @param array $parameters Parameter override (empty will use annotated parameters)
     * @param array $groups Group whitelist (empty implies all groups)
     */
    public function __construct()
    {
        $this->parser = new Parser();
    }

    public function buildSubjects(
        Benchmark $benchmark, 
        array $subjectsOverride = null,
        array $groups = null,
        array $parametersOverride = null
    )
    {
        $reflection = new \ReflectionClass(get_class($benchmark));
        $defaults = $this->parser->parseDoc($reflection->getDocComment());
        $methods = $reflection->getMethods();

        $subjects = array();
        foreach ($methods as $method) {
            if (0 !== strpos($method->getName(), 'bench')) {
                continue;
            }

            // if we have a subject whitelist, only include subjects in that whitelistd
            if ($subjectsOverride && false === in_array($method->getName(), $subjectsOverride)) {
                continue;
            }

            $meta = $this->parser->parseDoc($method->getDocComment(), $defaults);

            if ($groups && 0 === count(array_intersect($groups, $meta['group']))) {
                continue;
            }

            if (empty($meta['revs'])) {
                $meta['revs'] = array(1);
            }

            $this->createSubjects($subjects, $parametersOverride, $benchmark, $method, $meta);
        }

        return $subjects;
    }

    private function createSubjects(&$subjects, array $parameters = null, Benchmark $benchmark, \ReflectionMethod $method, array $meta)
    {
        $parameterSets = $this->getParameterSets($benchmark, $meta['paramProvider'], $parameters);
        $paramsIterator = new CartesianParameterIterator($parameterSets);

        foreach ($paramsIterator as $parameters) {
            $subjects[] = new Subject(
                self::$subjectIdCounter++,
                $method->getName(),
                $meta['beforeMethod'],
                $parameters,
                $meta['iterations'],
                $meta['revs'],
                $meta['description'],
                $meta['processIsolation'],
                $meta['group']
            );
        }
    }

    private function getParameterSets(Benchmark $benchmark, array $paramProviderMethods, $parameters)
    {
        if ($parameters) {
            return array(array($parameters));
        }

        $parameterSets = array();

        foreach ($paramProviderMethods as $paramProviderMethod) {
            if (!method_exists($benchmark, $paramProviderMethod)) {
                throw new InvalidArgumentException(sprintf(
                    'Unknown param provider "%s" for bench benchmark "%s"',
                    $paramProviderMethod, get_class($benchmark)
                ));
            }
            $parameterSets[] = $benchmark->$paramProviderMethod();
        }

        if (!$parameterSets) {
            $parameterSets = array(array(array()));
        }

        return $parameterSets;
    }
}
