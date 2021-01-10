<?php

namespace PhpBench\Tests\Unit\Progress;

use Closure;
use Generator;
use PhpBench\Assertion\AssertionResult;
use PhpBench\Model\ParameterSet;
use PhpBench\Model\Result\TimeResult;
use PhpBench\Model\Variant;
use PhpBench\Progress\VariantSummaryFormatter;
use PhpBench\Tests\Util\TestUtil;
use PhpBench\Util\TimeUnit;
use PHPUnit\Framework\TestCase;

class VariantSummaryFormatterTest extends TestCase
{
    /**
     * @dataProvider provideFormat
     */
    public function testFormat(Closure $factory, string $format, string $expected): void
    {
        $variant = $factory(TestUtil::getVariant());
        $variant->getSubject()->setOutputTimePrecision(2);
        self::assertEquals($expected, self::createFormatter(
                $format,
                $format
            )->formatVariant($variant));
    }
        
    /**
     * @return Generator<mixed>
     */
    public function provideFormat(): Generator
    {
        yield 'empty' => [
                function (Variant $variant): Variant {
                    return $variant;
                },
                '',
                ''
            ];

        yield 'pre-calculated variant fields' => [
                function (Variant $variant): Variant {
                    return $variant;
                },
                '%variant.min% %variant.max% %variant.mean% %variant.mode% %variant.stdev%',
                '2.00 4.00 3.00 3.00 1.00'
            ];

        yield 'difference to baseline' => [
                function (Variant $variant): Variant {
                    $this->createBaseline($variant, 10);

                    return $variant;
                },
                '%percent_difference%',
                '+200.00'
            ];

        yield 'result with no assertion' => [
                function (Variant $variant): Variant {
                    $this->createBaseline($variant);

                    return $variant;
                },
                '<%result_style%>',
                '<result-none>'
            ];

        yield 'assertion tolerated' => [
                function (Variant $variant): Variant {
                    $variant->addAssertionResult(AssertionResult::tolerated('ok'));
                    $this->createBaseline($variant);

                    return $variant;
                },
                '%result_style%',
                'result-neutral'
            ];

        yield 'assertion fail' => [
                function (Variant $variant): Variant {
                    $variant->addAssertionResult(AssertionResult::fail('fail'));
                    $this->createBaseline($variant);

                    return $variant;
                },
                '%result_style%',
                'result-failure'
            ];
    }

    private static function createFormatter(string $format, string $baselineFormat): VariantSummaryFormatter
    {
        return new VariantSummaryFormatter(
            new TimeUnit(),
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
