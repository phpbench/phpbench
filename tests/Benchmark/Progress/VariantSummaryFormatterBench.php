<?php

namespace PhpBench\Tests\Benchmark\Progress;

use PhpBench\Model\Result\TimeResult;
use PhpBench\Model\Variant;
use PhpBench\Progress\VariantFormatter;
use PhpBench\Tests\Benchmark\IntegrationBenchCase;
use PhpBench\Tests\Util\VariantBuilder;

class VariantSummaryFormatterBench extends IntegrationBenchCase
{
    private readonly VariantFormatter $formatter;

    private readonly Variant $variant;

    public function __construct()
    {
        $this->formatter = $this->container()->get(VariantFormatter::class);

        $variant = VariantBuilder::create()
            ->setRevs(100);

        for ($i = 0; $i < 100; $i++) {
            $variant->iteration()->setResult(
                new TimeResult(100, 1)
            );
        }

        $baseline = VariantBuilder::create()
            ->setRevs(100);

        for ($i = 0; $i < 100; $i++) {
            $baseline->iteration()->setResult(
                new TimeResult(100, 1),
            );
        }


        $variant = $variant->build();
        $variant->attachBaseline($baseline->build());
        $this->variant = $variant;
    }

    public function benchFormat(): void
    {
        $this->formatter->formatVariant($this->variant);
    }
}
