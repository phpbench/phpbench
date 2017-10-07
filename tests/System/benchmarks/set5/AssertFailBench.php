<?php

namespace PhpBench\Tests\System\benchmarks\set5;

/**
 * @Assert(stat="mean", value="1000", comparator=">")
 */
class AssertFailBench
{
    public function benchFail()
    {
    }
}
