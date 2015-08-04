<?php

namespace PhpBench\Tests\Unit\Report\Tool;

use PhpBench\Report\Tool\Assert;

class AssertTest extends \PHPUnit_Framework_TestCase
{
    /**
     * It should assert if an array contains only the given keys
     * It should do nothing if the array does only contain the given keys
     */
    public function testArrayOnlyKeys()
    {
        Assert::hasOnlyKeys(array(
            'one', 'two'
        ), array('one' => 'hello'), 'this context');
    }

    /**
     * It should assert if an array contains only the given keys
     * It should throw an exception if an array has keys which are not in the given set.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid keys for this context: "foobar". Valid keys are: "one", "two"
     */
    public function testArrayOnlyKeysFail()
    {
        Assert::hasOnlyKeys(array(
            'one', 'two'
        ), array('foobar' => 'hello'), 'this context');
    }
}
