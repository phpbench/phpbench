<?php

namespace PhpBench\Tests\Unit\Progress;

use PhpBench\Assertion\ParameterProvider;
use PhpBench\Expression\ExpressionLanguage;
use PhpBench\Expression\Printer\EvaluatingPrinter;
use PhpBench\Model\ParameterSet;
use PhpBench\Model\Result\TimeResult;
use PhpBench\Model\Variant;
use PhpBench\Progress\VariantSummaryFormatter;
use PhpBench\Tests\IntegrationTestCase;
use PhpBench\Tests\Util\TestUtil;

class VariantSummaryFormatterTest extends IntegrationTestCase
{
    public function testFormatVariantOnly(): void
    {
        $variant = TestUtil::getVariant();
        self::assertEquals('variant: 5', $this->createFormatter(
            '"variant: " ~ mode(variant.time.avg)',
            '"baseline: " ~ mode(variant.baseline.avg)'
        )->formatVariant($variant));
    }

    public function testFormatBaseline(): void
    {
        $variant = TestUtil::getVariant();
        $this->createBaseline($variant);
        self::assertEquals('baseline: 30', $this->createFormatter(
            '"variant: " ~ mode(variant.time.avg)',
            '"baseline: " ~ mode(baseline.time.avg)'
        )->formatVariant($variant));
    }

    private function createFormatter(string $format, string $baselineFormat): VariantSummaryFormatter
    {
        return new VariantSummaryFormatter(
            $this->container()->get(ExpressionLanguage::class),
            $this->container()->get(EvaluatingPrinter::class),
            $this->container()->get(ParameterProvider::class),
            $format,
            $baselineFormat
        );
    }

    private function createBaseline(Variant $variant, int $time = 30): void
    {
        $baseline = $variant->getSubject()->createVariant(
            ParameterSet::fromUnserializedValues('no', []),
            10,
            10,
            []
        );
        $baseline->spawnIterations(1);

        foreach ($baseline->getIterations() as $iteration) {
            $iteration->setResult(new TimeResult($time));
        }
        $baseline->computeStats();
        $variant->attachBaseline($baseline);
    }
}
