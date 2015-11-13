<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\System\benchmarks\set3;

class WritePIDBench
{
    private $written = false;

    public function benchWritePID()
    {
        if ($this->written) {
            return;
        }

        $handle = fopen(__DIR__ . '/pids', 'a');
        fwrite($handle, 'IN ');
        usleep(250000);
        fwrite($handle, 'OUT ');
        fclose($handle);
    }
}
