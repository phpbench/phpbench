<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Unit\Benchmark\Executor\microtimetest;

class ExecutorBench
{
    public function beforeMethod()
    {
        file_put_contents(__DIR__ . '/before_method.tmp', 'Before method executed');
    }

    public function afterMethod()
    {
        file_put_contents(__DIR__ . '/after_method.tmp', 'After method executed');
    }

    public function doSomething()
    {
        static $count = 0;
        $data = array();
        for ($i = 0; $i < 10000; $i++) {
            $data[] = 'hallo';
        }
        $count++;
        file_put_contents(__DIR__ . '/revs.tmp', $count);
    }

    public function parameterized($one, $three)
    {
        file_put_contents(__DIR__ . '/param.tmp', json_encode(array($one, $three)));
    }

    public function parameterizedBefore($one, $three)
    {
        file_put_contents(__DIR__ . '/parambefore.tmp', json_encode(array($one, $three)));
    }

    public function parameterizedAfter($one, $three)
    {
        file_put_contents(__DIR__ . '/paramafter.tmp', json_encode(array($one, $three)));
    }
}
