<?php

namespace PhpBench\Tests\Benchmark\Executor;

use function password_hash;

class TemplateBench
{
    public function benchNothing(): void
    {
    }

    public function benchMd5(): void
    {
        md5("hello");
    }

    public function benchBcrypt(): void
    {
        password_hash('hello', PASSWORD_BCRYPT);
    }
}
