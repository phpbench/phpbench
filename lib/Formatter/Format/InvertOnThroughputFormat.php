<?php

namespace PhpBench\Formatter\Format;

use PhpBench\Formatter\FormatInterface;
use PhpBench\Util\TimeUnit;

class InvertOnThroughputFormat implements FormatInterface
{
    /**
     * @var TimeUnit
     */
    private $timeUnit;

    public function __construct(TimeUnit $timeUnit)
    {
        $this->timeUnit = $timeUnit;
    }

    /**
     * {@inheritDoc}
     */
    public function format(string $subject, array $options): string
    {
        $mode = $this->timeUnit->resolveMode($options['mode']);

        if ($mode === TimeUnit::MODE_THROUGHPUT) {
            return (string)-(float)$subject;
        }

        return $subject;
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultOptions(): array
    {
        return [
            'mode' => TimeUnit::MODE_TIME
        ];
    }
}
