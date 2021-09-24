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

namespace PhpBench\Reflection;

use IteratorAggregate;

/**
 * Contains a reflected class (the "top" class) and all it's ancestors.
 *
 * @implements IteratorAggregate<int,ReflectionClass>
 */
class ReflectionHierarchy implements IteratorAggregate
{
    /**
     * @var ReflectionClass[] ordered by leaf class ("top") first
     */
    private $reflectionClasses;

    /**
     * @param ReflectionClass[] $reflectionClasses
     */
    public function __construct(array $reflectionClasses = [])
    {
        $this->reflectionClasses = $reflectionClasses;
    }

    /**
     * Add a reflection class.
     *
     */
    public function addReflectionClass(ReflectionClass $reflectionClass): void
    {
        $this->reflectionClasses[] = $reflectionClass;
    }

    public function getIterator(): \ArrayObject
    {
        return new \ArrayObject($this->reflectionClasses);
    }

    /**
     * Return the "top" class.
     *
     * @throws \InvalidArgumentException
     */
    public function getTop(): ReflectionClass
    {
        if (!isset($this->reflectionClasses[0])) {
            throw new \InvalidArgumentException(
                'Cannot get top reflection class, reflection hierarchy is empty.'
            );
        }

        return $this->reflectionClasses[0];
    }

    /**
     * Return true if the class hierarchy contains the named method.
     *
     */
    public function hasMethod(string $name): bool
    {
        foreach ($this->reflectionClasses as $reflectionClass) {
            if (isset($reflectionClass->methods[$name])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Return true if the class hierarchy contains the named static method.
     *
     */
    public function hasStaticMethod(string $name): bool
    {
        foreach ($this->reflectionClasses as $reflectionClass) {
            if (isset($reflectionClass->methods[$name])) {
                $method = $reflectionClass->methods[$name];

                if ($method->isStatic) {
                    return true;
                }

                break;
            }
        }

        return false;
    }

    /**
     * Return true if there are no reflection classes here.
     */
    public function isEmpty(): bool
    {
        return 0 === count($this->reflectionClasses);
    }
}
