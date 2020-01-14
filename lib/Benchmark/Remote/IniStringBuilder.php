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

namespace PhpBench\Benchmark\Remote;

class IniStringBuilder
{
    public function build(array $config): string
    {
        $string = [];

        foreach ($config as $key => $values) {
            $values = (array) $values;

            foreach ($values as $value) {
                $string[] = sprintf('-d%s=%s', $key, $value);
            }
        }

        return implode(' ', $string);
    }
}
