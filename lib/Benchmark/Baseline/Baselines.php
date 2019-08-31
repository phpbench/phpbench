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

namespace PhpBench\Benchmark\Baseline;

/**
 * Class providing some static methods which are used
 * to provide base line measurements.
 *
 * @see \PhpBench\Benchmark\BaselineManager
 */
class Baselines
{
    /**
     * Do nothing.
     */
    public static function nothing($revs)
    {
        for ($i = 0; $i < $revs; $i++) {
        }
    }

    /**
     * Calculate an md5 hash.
     */
    public static function md5($revs)
    {
        for ($i = 0; $i < $revs; $i++) {
            md5('lorem ipusm');
        }
    }

    /**
     * Open a file, write a string to it $revs times, then
     * read each line back.
     */
    public static function fwriteFread($revs)
    {
        $tempName = tempnam(sys_get_temp_dir(), 'phpbench_baseline');
        $handle = fopen($tempName, 'w');

        for ($i = 0; $i < $revs; $i++) {
            fwrite($handle, 'lorum ipsum');
        }

        fclose($handle);

        $handle = fopen($tempName, 'r');

        $line = true;

        while ($line) {
            $line = fgets($handle);
        }

        fclose($handle);
    }
}
