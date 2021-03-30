<?php

namespace PhpBench\Tests\Benchmark\Progress;

use PhpBench\Model\Result\MemoryResult;
use PhpBench\Model\Result\TimeResult;
use PhpBench\Model\Variant;
use PhpBench\Progress\VariantFormatter;
use PhpBench\Tests\Benchmark\IntegrationBenchCase;
use PhpBench\Tests\Util\VariantBuilder;

class VariantSummaryFormatterBench extends IntegrationBenchCase
{
    /**
     * @var VariantFormatter
     */
    private $formatter;

    /**
     * @var Variant
     */
    private $variant;

    public function __construct()
    {
        $this->formatter = $this->container()->get(VariantFormatter::class);

        $variant = VariantBuilder::create()
            ->setRevs(100);

        for ($i = 0; $i < 100; $i++) {
            $variant->iteration()->setResult(
                new TimeResult(100, 1),
                new MemoryResult(100, 100, 100)
            );
        }

        $baseline = VariantBuilder::create()
            ->setRevs(100);

        for ($i = 0; $i < 100; $i++) {
            $baseline->iteration()->setResult(
                new TimeResult(100, 1),
                new MemoryResult(100, 100, 100)
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
