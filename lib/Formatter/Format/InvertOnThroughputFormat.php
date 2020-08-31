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
    public function format($subject, array $options)
    {
        $mode = $this->timeUnit->resolveMode($options['mode']);

        if ($mode === TimeUnit::MODE_THROUGHPUT) {
            return -(float)$subject;
        }

        return $subject;
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultOptions()
    {
        return [
            'mode' => TimeUnit::MODE_TIME
        ];
    }
}
