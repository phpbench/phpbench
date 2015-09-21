<?php

/**
 * @revs 1000
 * @iterations 10
 * @group hashing
 */
class HashingBenchmark
{
    public function benchMd5()
    {
        hash('md5', rand(0, 100000));
    }

    public function benchSha256()
    {
        hash('sha256', rand(0, 100000));
    }

    public function benchSha1()
    {
        hash('sha1', rand(0, 100000));
    }
}
