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

use ReflectionClass;

/**
 * @revs(10000)
 *
 * @iterations(5)
 */
class ClassWithClassKeywords
{
    protected $class = B::class;

    public function benchIsSubclassOf(): void
    {
        is_subclass_of($this->class, A::class);
    }

    public function benchReflectionClass(): void
    {
        $c = new ReflectionClass($this->class);
        $c->isSubclassOf(A::class);
    }
}

class A
{
}
