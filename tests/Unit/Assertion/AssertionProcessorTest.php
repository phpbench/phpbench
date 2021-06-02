<?php

namespace PhpBench\Tests\Unit\Assertion;

use Generator;
use PhpBench\Assertion\AssertionProcessor;
use PhpBench\Assertion\AssertionResult;
use PhpBench\Assertion\ParameterProvider;
use PhpBench\Expression\Evaluator;
use PhpBench\Expression\ExpressionLanguage;
use PhpBench\Expression\Printer;
use PhpBench\Expression\Printer\EvaluatingPrinter;
use PhpBench\Model\Result\TimeResult;
use PhpBench\Model\Variant;
use PhpBench\Tests\IntegrationTestCase;
use PhpBench\Tests\Util\VariantBuilder;

class AssertionProcessorTest extends IntegrationTestCase
{
    /**
     * @dataProvider provideAssert
     */
    public function testAssert(Variant $variant, string $assertion, AssertionResult $expected): void
    {
        self::assertEquals(
            $expected->type(),
            $this->createProcessor()->assert($variant, $assertion)->type()
        );
    }

    /**
     * @return Generator<mixed>
     */
    public function provideAssert(): Generator
    {
        yield [
                VariantBuilder::create()
                    ->setRevs(2)
                    ->iteration()
                        ->setResult(new TimeResult(10, 1))
                    ->end()
                ->build(),
                'mode(variant.time.avg) < 5',
                AssertionResult::fail()
            ];

        yield [
                VariantBuilder::create()
                    ->setRevs(2)
                    ->iteration()
                        ->setResult(new TimeResult(10, 1))
                    ->end()
                ->build(),
                'mode(variant.time.avg) < 5 +/- 5',
                AssertionResult::tolerated()
            ];

        yield [
                VariantBuilder::create()
                    ->setRevs(2)
                    ->iteration()
                        ->setResult(new TimeResult(10, 1))
                    ->end()
                ->build(),
                'mode(variant.time.avg) > 5',
                AssertionResult::ok()
            ];
    }

    private function createProcessor(): AssertionProcessor
    {
        return new AssertionProcessor(
            $this->container()->get(ExpressionLanguage::class),
            $this->container()->get(Evaluator::class),
            $this->container()->get(Printer::class),
            $this->container()->get(EvaluatingPrinter::class),
            new ParameterProvider()
        );
    }
}
