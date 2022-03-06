<?php

namespace PhpBench\Tests\Benchmark;

class MicroBench
{
    public function benchNoOp(): void
    {
    }

    public function benchMd5(): void
    {
        md5('a');
    }

    public function benchSha1(): void
    {
        hash('sha1', 'asd');
    }
    public function benchSha256(): void
    {
        hash('sha256', 'asd');
    }
}
