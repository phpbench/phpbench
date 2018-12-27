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

namespace PhpBench\Benchmark\Remote;

/**
 * Contains a reflected class (the "top" class) and all it's ancestors.
 */
class ReflectionHierarchy implements \IteratorAggregate
{
    /**
     * @var ReflectionClass[]
     */
    private $reflectionClasses = [];

    /**
     * Add a reflection class.
     *
     * @param ReflectionClass $reflectionClass
     */
    public function addReflectionClass(ReflectionClass $reflectionClass)
    {
        $this->reflectionClasses[] = $reflectionClass;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayObject($this->reflectionClasses);
    }

    /**
     * Return the "top" class.
     *
     * @throws \InvalidArgumentException
     *
     * @return ReflectionClass
     */
    public function getTop()
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
     * @param string $name
     *
     * @return bool
     */
    public function hasMethod($name)
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
     * @param string $name
     *
     * @return bool
     */
    public function hasStaticMethod($name)
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
     *
     * @return bool
     */
    public function isEmpty()
    {
        return 0 === count($this->reflectionClasses);
    }
}
