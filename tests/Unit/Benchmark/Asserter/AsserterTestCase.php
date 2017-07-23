<?php

namespace PhpBench\Tests\Unit\Benchmark\Asserter;

use PHPUnit\Framework\TestCase;
use PhpBench\Benchmark\Asserter\SymfonyAsserter;
use PhpBench\Math\Distribution;
use PhpBench\Benchmark\AsserterInterface;
use PhpBench\Benchmark\AssertionFailure;

abstract class AsserterTestCase extends TestCase
{
    /**
     * @dataProvider provideAssert
     */
    public function testAssert($expression, array $samples, bool $expectFailure)
    {
        if ($expectFailure) {
            $this->expectException(AssertionFailure::class, 'Assertion "' . $expression . '" failed');
        } else {
            $this->addToAssertionCount(1);
        }
        
        $this->asserter()->assert($expression, new Distribution($samples));

    }

    public function provideAssert()
    {
        return [
            [
                'stats.mean < 100',
                [ 150, 150, 150 ],
                true,
            ],
            [
                'stats.mean > 100 and stats.min > 200',
                [ 150, 150, 150 ],
                true,
            ],
            [
                'stats.mode < 100',
                [ 10, 15, 15 ],
                false,
            ],
        ];
    }

    abstract protected function asserter(): AsserterInterface;
}
