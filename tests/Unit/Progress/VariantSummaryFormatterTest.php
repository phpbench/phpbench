<?php

namespace PhpBench\Tests\Unit\Progress;

use Closure;
use Generator;
use PhpBench\Assertion\AssertionResult;
use PhpBench\Assertion\ParameterProvider;
use PhpBench\Expression\ExpressionLanguage;
use PhpBench\Expression\Printer;
use PhpBench\Expression\Printer\EvaluatingPrinter;
use PhpBench\Expression\SyntaxHighlighter;
use PhpBench\Model\ParameterSet;
use PhpBench\Model\Result\TimeResult;
use PhpBench\Model\Variant;
use PhpBench\Progress\VariantSummaryFormatter;
use PhpBench\Tests\IntegrationTestCase;
use PhpBench\Tests\Util\TestUtil;
use PhpBench\Util\TimeUnit;
use PHPUnit\Framework\TestCase;

class VariantSummaryFormatterTest extends IntegrationTestCase
{
    public function testFormatVariantOnly(): void
    {
        $variant = TestUtil::getVariant();
        self::assertEquals('variant: <fg=cyan>15.01</>', $this->createFormatter(
                '"variant: " ~ mode(variant.time.avg)',
                '"baseline: " ~ mode(variant.baseline.avg)',
            )->formatVariant($variant));
    }

    public function testFormatBaseline(): void
    {
        $variant = TestUtil::getVariant();
        $this->createBaseline($variant);
        self::assertEquals('baseline: <fg=cyan>30</>', $this->createFormatter(
                '"variant: " ~ mode(variant.time.avg)',
                '"baseline: " ~ mode(baseline.time.avg)',
            )->formatVariant($variant));
    }

    private function createFormatter(string $format, string $baselineFormat): VariantSummaryFormatter
    {
        return new VariantSummaryFormatter(
            $this->container()->get(ExpressionLanguage::class),
            $this->container()->get(EvaluatingPrinter::class),
            $this->container()->get(ParameterProvider::class),
            $this->container()->get(SyntaxHighlighter::class),
            $format,
            $baselineFormat
        );
    }

    private function createBaseline(Variant $variant, int $time = 30): void
    {
        $baseline = $variant->getSubject()->createVariant(
            new ParameterSet('no',[]), 10, 10, []
        );
        $baseline->spawnIterations(1);

        foreach ($baseline->getIterations() as $iteration) {
            $iteration->setResult(new TimeResult($time));
        }
        $baseline->computeStats();
        $variant->attachBaseline($baseline);
    }
}
