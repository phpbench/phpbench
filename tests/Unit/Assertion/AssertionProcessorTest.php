<?php

namespace PhpBench\Tests\Unit\Assertion;

use Generator;
use PHPUnit\Framework\TestCase;
use PhpBench\Assertion\AssertionProcessor;
use PhpBench\Assertion\AssertionResult;
use PhpBench\Assertion\Exception\ExpressionError;
use PhpBench\Assertion\Exception\ExpressionEvaluatorError;
use PhpBench\Model\Result\TimeResult;
use PhpBench\Tests\IntegrationTestCase;
use PhpBench\Tests\Util\SuiteBuilder;
use PhpBench\Tests\Util\VariantBuilder;

class AssertionProcessorTest extends IntegrationTestCase
{
    /**
     * @dataProvider provideAssertionResult
     */
    public function testAssertionReuslt(string $assertion, AssertionResult $expected): void
    {
        $variant = VariantBuilder::create(
            'one'
        )->iteration()->setResult(
            new TimeResult(10)
        )->end()->build();

        $processor = $this->createProcessor();
        $result = $processor->assert($variant, $assertion);
        self::assertEquals($expected, $result);
    }

    /**
     * @return Generator<mixed>
     */
    public function provideAssertionResult(): Generator
    {
        yield [
            'mode(variant.time.net) = mode(variant.time.net)',
            AssertionResult::ok()
        ];
        yield [
            'mode(variant.time.net) < mode(variant.time.net)',
            AssertionResult::fail('10 < 10')
        ];
        yield [
            'mode(variant.time.net) < mode(variant.time.net) +/- 100',
            AssertionResult::tolerated('10 < 10 ± 100')
        ];
    }

    public function testExceptionIfNodeNotAComparison(): void
    {
        $this->expectException(ExpressionEvaluatorError::class);
        $variant = VariantBuilder::create(
            'one'
        )->iteration()->setResult(
            new TimeResult(10)
        )->end()->build();

        $this->createProcessor()->assert($variant, '12');
    }

    public function testEvaluationError(): void
    {
        $this->expectException(ExpressionError::class);
        $variant = VariantBuilder::create(
            'one'
        )->iteration()->setResult(
            new TimeResult(10)
        )->end()->build();

        $this->createProcessor()->assert($variant, 'mode(foo.bar) > 10');
    }

    private function createProcessor(): AssertionProcessor
    {
        $processor = $this->container()->get(AssertionProcessor::class);
        assert($processor instanceof AssertionProcessor);
        return $processor;
    }

}
