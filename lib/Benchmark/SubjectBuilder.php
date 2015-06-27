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
    static $subjectIdCounter = 0;

    private $parser;
    private $subjects;
    private $groups;
    private $parameters;

    /**
     * @param array $subjects Subject whitelist (empty implies all subjects)
     * @param array $parameters Parameter override (empty will use annotated parameters)
     * @param array $groups Group whitelist (empty implies all groups)
     */
    public function __construct(array $subjects = array(), array $parameters = array(), array $groups = array())
    {
        $this->parser = new Parser();
        $this->subjects = $subjects;
        $this->groups = $groups;
        $this->parameters = $parameters;
    }

    public function buildSubjects(Benchmark $benchmark)
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
            if ($this->subjects && false === in_array($method->getName(), $this->subjects)) {
                continue;
            }

            $meta = $this->parser->parseDoc($method->getDocComment(), $defaults);

            if ($this->groups && 0 === count(array_intersect($this->groups, $meta['group']))) {
                continue;
            }

            if (empty($meta['revs'])) {
                $meta['revs'] = array(1);
            }

            $this->createSubjects($subjects, $benchmark, $method, $meta);
        }

        return $subjects;
    }

    private function createSubjects(&$subjects, Benchmark $benchmark, \ReflectionMethod $method, array $meta)
    {
        $parameterSets = $this->getParameterSets($benchmark, $meta['paramProvider']);
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

    private function getParameterSets(Benchmark $benchmark, array $paramProviderMethods)
    {
        if ($this->parameters) {
            return array(array($this->parameters));
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
