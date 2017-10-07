<?php

namespace PhpBench\Tests\Unit\Assertion;

use PHPUnit\Framework\TestCase;
use PhpBench\Assertion\ComparatorAssertion;
use PhpBench\Math\Distribution;
use PhpBench\Registry\Config;
use PhpBench\Assertion\AssertionFailure;
use PhpBench\Assertion\AssertionRegistry;
use PhpBench\DependencyInjection\Container;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ComparatorAssertionTest extends TestCase
{
    const COMPARATOR_ASSERTION = 'comparator';

    /**
     * @dataProvider provideComparison
     */
    public function testComparison(string $stat, $expectedValue, array $samples, array $config, string $failureMessage = null)
    {
        if ($failureMessage) {
            $this->expectException(AssertionFailure::class);
            $this->expectExceptionMessage($failureMessage);
        }

        $this->assert($stat, $expectedValue, new Distribution($samples), $config);

        if (null === $failureMessage) {
            $this->addToAssertionCount(1);
        }
    }

    public function provideComparison()
    {
        return [
            [
                'mean',
                15,
                [ 10, 10 ],
                [],
            ],
            [
                'mean',
                5,
                [ 10, 10 ],
                [],
                'mean is not less than 5, it was 10',
            ],
            [
                'mean',
                5,
                [ 2, 2 ],
                [
                    self::COMPARATOR_ASSERTION => '>',
                ],
                'mean is not greater than 5, it was 2',
            ],
        ];
    }

    private function assert(string $stat, $expectedValue, Distribution $distribution, array $config = [])
    {
        $assertion = new ComparatorAssertion();
        $optionsResolver = new OptionsResolver();
        $assertion->configure($optionsResolver);
        $config = $optionsResolver->resolve($config);

        $assertion->assert($stat, $expectedValue, $distribution, new Config('test', $config));
    }
}
