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

namespace PhpBench\Tests\Unit\Assertion;

use PhpBench\Assertion\AssertionData;
use PhpBench\Assertion\AssertionFailure;
use PhpBench\Assertion\AssertionWarning;
use PhpBench\Assertion\ComparatorAsserter;
use PhpBench\Math\Distribution;
use PhpBench\Registry\Config;
use PhpBench\Util\TimeUnit;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ComparatorAsserterTest extends TestCase
{
    const COMPARATOR_ASSERTION = 'comparator';

    /**
     * @dataProvider provideComparison
     */
    public function testComparison(array $samples, array $config, string $failureMessage = null, string $exceptionClass = AssertionFailure::class)
    {
        if ($failureMessage) {
            $this->expectException($exceptionClass);
            $this->expectExceptionMessage($failureMessage);
        }

        $this->assert(AssertionData::fromDistribution(new Distribution($samples)), $config);

        if (null === $failureMessage) {
            $this->addToAssertionCount(1);
        }
    }

    public function provideComparison()
    {
        return [
            [
                [10, 10],
                [
                    ComparatorAsserter::OPTION_STAT => 'mean',
                    ComparatorAsserter::OPTION_VALUE => 15,

                ],
            ],
            [
                [10, 10],
                [
                    ComparatorAsserter::OPTION_STAT => 'mean',
                    ComparatorAsserter::OPTION_VALUE => 5,
                ],
                'mean is not less than 5.000μs, it was 10.000μs',
            ],
            [
                [2, 2],
                [
                    ComparatorAsserter::OPTION_COMPARATOR => '>',
                    ComparatorAsserter::OPTION_STAT => 'mean',
                    ComparatorAsserter::OPTION_VALUE => 5,
                ],
                'mean is not greater than 5.000μs, it was 2.000μs',
            ],
            [
                [2000, 2000],
                [
                    ComparatorAsserter::OPTION_STAT => 'mean',
                    ComparatorAsserter::OPTION_VALUE => 1,
                    ComparatorAsserter::OPTION_TIME_UNIT => 'milliseconds',
                ],
                'mean is not less than 1.000ms, it was 2.000ms',
            ],
            [
                [2000, 2000],
                [
                    ComparatorAsserter::OPTION_STAT => 'mean',
                    ComparatorAsserter::OPTION_VALUE => 1,
                    ComparatorAsserter::OPTION_TIME_UNIT => 'milliseconds',
                    ComparatorAsserter::OPTION_COMPARATOR => '>',
                    ComparatorAsserter::OPTION_MODE => 'throughput',
                ],
                'Throughput for mean is not greater than 1.000ops/ms, it was 0.500ops/ms',
            ],
            'tolerance lower' => [
                [2000, 2000],
                [
                    ComparatorAsserter::OPTION_STAT => 'mean',
                    ComparatorAsserter::OPTION_VALUE => 1999,
                    ComparatorAsserter::OPTION_TOLERANCE => 2,
                ],
                'mean is not less than 1,999.000μs, it was 2,000.000μs',
                AssertionWarning::class,
            ],
            'tolerance upper' => [
                [2000, 2000],
                [
                    ComparatorAsserter::OPTION_STAT => 'mean',
                    ComparatorAsserter::OPTION_VALUE => 2001,
                    ComparatorAsserter::OPTION_TOLERANCE => 2,
                    ComparatorAsserter::OPTION_COMPARATOR => '>',
                ],
                'mean is not',
                AssertionWarning::class,
            ],
            'tolerance time unit' => [
                [2000, 2000],
                [
                    ComparatorAsserter::OPTION_STAT => 'mean',
                    ComparatorAsserter::OPTION_VALUE => 1,
                    ComparatorAsserter::OPTION_TOLERANCE => 2,
                    ComparatorAsserter::OPTION_TIME_UNIT => 'milliseconds',
                ],
                'mean is not',
                AssertionWarning::class,
            ],
            'tolerance throughput' => [
                [2000, 2000],
                [
                    ComparatorAsserter::OPTION_STAT => 'mean',
                    ComparatorAsserter::OPTION_VALUE => 1,
                    ComparatorAsserter::OPTION_COMPARATOR => '>',
                    ComparatorAsserter::OPTION_TIME_UNIT => 'milliseconds',
                    ComparatorAsserter::OPTION_TOLERANCE => 2,
                    ComparatorAsserter::OPTION_MODE => 'throughput',
                ],
                'Throughput for mean is not greater than 1.000ops/ms, it was 0.500ops/ms',
                AssertionWarning::class,
            ],
        ];
    }

    private function assert(AssertionData $data, array $config = [])
    {
        $assertion = new ComparatorAsserter(new TimeUnit());
        $optionsResolver = new OptionsResolver();
        $assertion->configure($optionsResolver);
        $config = $optionsResolver->resolve($config);

        $assertion->assert($data, new Config('test', $config));
    }
}
