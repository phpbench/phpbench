<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @Groups({"cost_of_calling", "group_two"})
 * @Revs(10000)
 * @Iterations(10)
 */
class CostOfCalling
{
    private $reflectionMethod;

    public function benchCallWithoutParams()
    {
        $this->doSomething();
    }

    public function benchCallWithParams()
    {
        $this->doSomethingWithParams(1, 2, 3, 4);
    }

    public function benchUserFuncWithoutParams()
    {
        call_user_func(array($this, 'doSomething'));
    }

    public function benchUserFuncParams()
    {
        call_user_func(array($this, 'doSomething'), 1, 2, 3, 4);
    }

    /**
     * @BeforeMethods({"initReflection"})
     */
    public function benchReflectionCall()
    {
        $this->reflectionMethod->invoke($this);
    }

    public function initReflection()
    {
        $reflection = new ReflectionClass($this);
        $this->reflectionMethod = $reflection->getMethod('doSomething');
    }

    /**
     * @BeforeMethods({"initReflectionWithParams"})
     */
    public function benchReflectionCallWithParams()
    {
        $this->reflectionMethod->invokeArgs($this, array(1, 2, 3, 4));
    }

    public function initReflectionWithParams()
    {
        $reflection = new ReflectionClass($this);
        $this->reflectionMethod = $reflection->getMethod('doSomethingWithParams');
    }

    public function doSomething()
    {
    }

    public function doSomethingWithParams($one, $two, $three, $four)
    {
    }
}
