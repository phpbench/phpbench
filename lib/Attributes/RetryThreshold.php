<?php

namespace PhpBench\Attributes;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
final class RetryThreshold
{
    /**
     * @var int
     */
    public $retryThreshold;

    public function __construct(int|float $retryThreshold)
    {
        $this->retryThreshold = $retryThreshold;
    }
}
