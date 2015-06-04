<?php

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
     * @description Set public class property via. a setter
     */
    public function benchMethodSet()
    {
        $this->class->setPublic('hello');
    }

    /**
     * @description Set public class property
     */
    public function benchPublicProperty()
    {
        $this->class->public = 'hello';
    }

    /**
     * @description Set public class property via. reflection
     */
    public function benchPublicReflection()
    {
        $this->reflectionPublicProp->setValue($this->class, 'hello');
    }

    /**
     * @description Set private class property via. reflection
     */
    public function benchPrivateReflection()
    {
        $this->reflectionPrivateProp->setValue($this->class, 'hello');
    }

    /**
     * @description Instantiate new class
     */
    public function benchNewClass()
    {
        new TestClass;
    }

    /**
     * @description Reflection new class
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
