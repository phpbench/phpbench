<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @Revs(1000)
 * @Iterations(8)
 * @Groups({"string_extraction"})
 */
class StringSplitting
{
    const SUBJECT = 'group#foobar';

    public function benchStrStrSubstr()
    {
        $group = strstr(self::SUBJECT, '#', true);
        $foobar = substr(self::SUBJECT, strlen($group) + 1);

        $this->checkExpected($group, $foobar);
    }

    public function benchPregMatch()
    {
        preg_match('{^(.*?)#(.*)$}', self::SUBJECT, $matches);
        $this->checkExpected($matches[1], $matches[2]);
    }

    public function benchExplode()
    {
        $parts = explode('#', self::SUBJECT);
        $this->checkExpected($parts[0], $parts[1]);
    }

    private function checkExpected($group, $foobar)
    {
        if ($group !== 'group' || $foobar !== 'foobar') {
            throw new \InvalidArgumentException('Inconsistent benchmark result');
        }
    }
}
