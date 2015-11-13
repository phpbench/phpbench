<?php

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
        usleep(50000);
        fwrite($handle, 'OUT ');
        fclose($handle);
    }
}
