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

namespace PhpBench\Benchmarks\Micro;

/**
 * @Revs(1000)
 * @Iterations(10)
 */
class HashingBench
{
    public function benchMd5()
    {
        return md5('hello world');
    }

    public function benchSha1()
    {
        return sha1('hello world');
    }

    public function benchSha256()
    {
        return hash('sha256', 'hello world');
    }
}
