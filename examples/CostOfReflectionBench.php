<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PhpBench\Benchmark;

/**
 * @beforeMethod init
 * @revs 10000
 * @iterations 4
 */
class CostOfReflectionBench implements Benchmark
{
    private $class;
    private $reflection;
    private $reflectionPublicProp;
    private $reflectionPrivateProp;

    public function init()
    {
        $this->class = new TestClass();
        $this->reflection = new \ReflectionClass('TestClass');
        $this->reflectionPublicProp = $this->reflection->getProperty('public');
        $this->reflectionPrivateProp = $this->reflection->getProperty('public');
        $this->reflectionPrivateProp->setAccessible(true);
    }

    /**
     * @group cost_of_setting
     */
    public function benchMethodSet()
    {
        $this->class->setPublic('hello');
    }

    /**
     * @group cost_of_setting
     */
    public function benchPublicProperty()
    {
        $this->class->public = 'hello';
    }

    /**
     * @group cost_of_setting
     */
    public function benchPublicReflection()
    {
        $this->reflectionPublicProp->setValue($this->class, 'hello');
    }

    /**
     * @group cost_of_setting
     */
    public function benchPrivateReflection()
    {
        $this->reflectionPrivateProp->setValue($this->class, 'hello');
    }

    /**
     * @group cost_of_instantiation
     */
    public function benchNewClass()
    {
        new TestClass();
    }

    /**
     * @group cost_of_instantiation
     */
    public function benchReflectionNewInstance()
    {
        $this->reflection->newInstance();
    }
}

class TestClass
{
    public $public;
    private $private;

    public function setPublic($foo)
    {
        $this->public = $foo;
    }
}
