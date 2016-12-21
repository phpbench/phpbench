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

class IsolatedBench
{
    /**
     * @Iterations(5)
     */
    public function benchIterationIsolation()
    {
        $handle = fopen(sys_get_temp_dir() . '/phpbench_isolationtest', 'a');
        fwrite($handle, getmypid() . PHP_EOL);
        fclose($handle);
    }

    /**
     * @Iterations(5)
     */
    public function benchIterationsIsolation()
    {
        $handle = fopen(sys_get_temp_dir() . '/phpbench_isolationtest', 'a');
        fwrite($handle, getmypid() . PHP_EOL);
        fclose($handle);
    }
}
