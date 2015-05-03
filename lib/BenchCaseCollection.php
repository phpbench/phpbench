<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench;

class BenchCaseCollection
{
    private $cases;

    public function __construct(array $cases)
    {
        $this->cases = $cases;
    }

    public function getCases()
    {
        return $this->cases;
    }
}
