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

namespace PhpBench\Tests\System\benchmarks\set3;

/**
 * This benchmark throws exceptions.
 */
class ErrorBench
{
    public function benchNothing()
    {
    }

    public function benchException()
    {
        throw new \Exception('Arff');
    }

    public function benchNothingElse()
    {
    }
}
