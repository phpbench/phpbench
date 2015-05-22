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
    private $subject;

    public function __construct($subject = null)
    {
        $this->parser = new Parser();
        $this->subject = $subject;
    }

    public function buildSubjects(Benchmark $case)
    {
        $reflection = new \ReflectionClass(get_class($case));
        $methods = $reflection->getMethods();

        $subjects = array();
        foreach ($methods as $method) {
            if (0 !== strpos($method->getName(), 'bench')) {
                continue;
            }

            if ($this->subject && $method->getName() !== $this->subject) {
                continue;
            }

            $meta = $this->parser->parseMethodDoc($method->getDocComment());

            $subjects[] = new Subject(
                $method->getName(),
                $meta['beforeMethod'],
                $meta['paramProvider'],
                $meta['iterations'],
                $meta['description']
            );
        }

        return $subjects;
    }
}
