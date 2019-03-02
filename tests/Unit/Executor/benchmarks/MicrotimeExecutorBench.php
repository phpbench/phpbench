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

namespace PhpBench\Tests\Unit\Executor\benchmarks;

class MicrotimeExecutorBench
{
    private static $workspaceDir = __DIR__ . '/../../../Workspace';

    public static function initDatabase()
    {
        file_put_contents(self::$workspaceDir . '/static_method.tmp', 'Static method method executed');
    }

    public function beforeMethod()
    {
        file_put_contents(self::$workspaceDir . '/before_method.tmp', 'Before method executed');
    }

    public function afterMethod()
    {
        file_put_contents(self::$workspaceDir . '/after_method.tmp', 'After method executed');
    }

    public function benchOutput()
    {
        // PHPBench should not crash if the user outputs something in their benchmark.
        echo 'Hello World';
    }

    public function doSomething()
    {
        static $count = 0;
        $data = [];

        for ($i = 0; $i < 10000; $i++) {
            $data[] = 'hallo';
        }
        $count++;
        file_put_contents(self::$workspaceDir . '/revs.tmp', $count);
    }

    public function parameterized($params)
    {
        file_put_contents(self::$workspaceDir . '/param.tmp', json_encode($params));
    }

    public function parameterizedBefore($params)
    {
        file_put_contents(self::$workspaceDir . '/parambefore.tmp', json_encode($params));
    }

    public function parameterizedAfter($params)
    {
        file_put_contents(self::$workspaceDir . '/paramafter.tmp', json_encode($params));
    }
}
