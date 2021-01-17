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

namespace PhpBench\Tests\System\benchmarks\set5;

/**
 * @Assert("variant.mean < 1 microseconds +/- 1000 microseconds")
 */
class AssertWarnBench
{
    public function benchFail(): void
    {
    }
}
