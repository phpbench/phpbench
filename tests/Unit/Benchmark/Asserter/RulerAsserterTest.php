<?php

namespace PhpBench\Tests\Unit\Benchmark\Asserter;

use PHPUnit\Framework\TestCase;
use PhpBench\Benchmark\Asserter\SymfonyAsserter;
use PhpBench\Math\Distribution;
use PhpBench\Benchmark\AsserterInterface;
use PhpBench\Benchmark\Asserter\RulerAsserter;

class RulerAsserterTest extends AsserterTestCase
{
    public function asserter(): AsserterInterface
    {
        return new RulerAsserter();
    }
}

