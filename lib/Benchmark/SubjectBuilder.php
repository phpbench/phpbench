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

class SubjectBuilder
{
    private $parser;
    private $subjects;
    private $groups;

    public function __construct(array $subjects = array(), array $groups = array())
    {
        $this->parser = new Parser();
        $this->subjects = $subjects;
        $this->groups = $groups;
    }

    public function buildSubjects(Benchmark $case)
    {
        $reflection = new \ReflectionClass(get_class($case));
        $defaults = $this->parser->parseDoc($reflection->getDocComment());
        $methods = $reflection->getMethods();

        $subjects = array();
        foreach ($methods as $method) {
            if (0 !== strpos($method->getName(), 'bench')) {
                continue;
            }

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

            $subjects[] = new Subject(
                $method->getName(),
                $meta['beforeMethod'],
                $meta['paramProvider'],
                $meta['iterations'],
                $meta['revs'],
                $meta['description'],
                $meta['processIsolation'],
                $meta['group']
            );
        }

        return $subjects;
    }
}
