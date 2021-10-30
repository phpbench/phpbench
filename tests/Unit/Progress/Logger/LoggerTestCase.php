<?php

namespace PhpBench\Tests\Unit\Progress\Logger;

use PhpBench\Model\Variant;
use PhpBench\Progress\VariantFormatter;
use PhpBench\Progress\VariantSummaryFormatter;
use PhpBench\Tests\TestCase;
use PhpBench\Util\TimeUnit;
use Symfony\Component\Console\Output\BufferedOutput;

class LoggerTestCase extends TestCase
{
    /**
     * @var TimeUnit
     */
    protected $timeUnit;

    /**
     * @var VariantSummaryFormatter
     */
    protected $variantFormatter;

    /**
     * @var BufferedOutput
     */
    protected $output;

    protected function setUp(): void
    {
        $this->output = new BufferedOutput();

        $this->timeUnit = new TimeUnit(TimeUnit::MICROSECONDS, TimeUnit::MILLISECONDS);
        $this->variantFormatter = new class () implements VariantFormatter {
            public function formatVariant(Variant $variant): string
            {
                return 'summary';
            }
        };
    }
}
