<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Test;

/**
 * @revs(10000)
 * @iterations(5)
 */
class ClassWithClassKeywords
{
    protected $class = \Test\B::class;

    public function benchIsSubclassOf()
    {
        is_subclass_of($this->class, \Test\A::class);
    }

    public function benchReflectionClass()
    {
        $c = new \ReflectionClass($this->class);
        $c->isSubclassOf(\Test\A::class);
    }
}

class A
{
}
