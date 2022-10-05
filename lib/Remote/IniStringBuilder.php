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

namespace PhpBench\Remote;

class IniStringBuilder
{
    /**
     * @deprecated use buildList instead
     *
     * @param array<string,mixed> $config
     */
    public function build(array $config): string
    {
        return implode(' ', $this->buildList($config));
    }

    /**
     * @param array<string,mixed> $config
     *
     * @return string[]
     */
    public function buildList(array $config): array
    {
        $args = [];

        foreach ($config as $key => $values) {
            $values = (array) $values;

            foreach ($values as $value) {
                $args[] = sprintf('-d%s=%s', $key, $value);
            }
        }

        return $args;
    }
}
