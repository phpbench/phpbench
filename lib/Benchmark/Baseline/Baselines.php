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
 */
class Baselines
{
    /**
     * Do nothing.
     *
     * @param int $revs
     */
    public static function nothing($revs): void
    {
        for ($i = 0; $i < $revs; $i++) {
        }
    }

    /**
     * Calculate an md5 hash.
     *
     * @param int $revs
     */
    public static function md5($revs): void
    {
        for ($i = 0; $i < $revs; $i++) {
            /** @phpstan-ignore-next-line */
            md5('lorem ipusm');
        }
    }

    /**
     * Open a file, write a string to it $revs times, then
     * read each line back.
     *
     * @param int $revs
     */
    public static function fwriteFread($revs): void
    {
        $tempName = tempnam(sys_get_temp_dir(), 'phpbench_baseline');

        if ($tempName === false) {
            throw new \RuntimeException('Failed to create a temp file');
        }
        $handle = fopen($tempName, 'w');

        if ($handle === false) {
            throw new \RuntimeException("Temp file $tempName is not writeable");
        }

        for ($i = 0; $i < $revs; $i++) {
            fwrite($handle, 'lorum ipsum');
        }

        fclose($handle);

        $handle = fopen($tempName, 'r');

        if ($handle === false) {
            throw new \RuntimeException("Temp file $tempName is not readable");
        }

        $line = true;

        while ($line) {
            $line = fgets($handle);
        }

        fclose($handle);
    }
}
