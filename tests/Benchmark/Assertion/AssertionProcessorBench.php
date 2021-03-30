<?php

namespace PhpBench\Tests\Benchmark\Assertion;

use PhpBench\Assertion\AssertionProcessor;
use PhpBench\Model\Result\MemoryResult;
use PhpBench\Model\Result\TimeResult;
use PhpBench\Model\Variant;
use PhpBench\Tests\Benchmark\IntegrationBenchCase;
use PhpBench\Tests\Util\VariantBuilder;

class AssertionProcessorBench extends IntegrationBenchCase
{
    /**
     * @var AssertionProcessor
     */
    private $processor;

    /**
     * @var Variant
     */
    private $variant;

    public function __construct()
    {
        $this->processor = $this->container()->get(AssertionProcessor::class);

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

    public function benchAssert(): void
    {
        $this->processor->assert($this->variant, 'mode(variant.time.avg) < mode(baseline.time.avg) +/- 10%');
    }
}
