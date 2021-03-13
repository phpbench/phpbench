<?php

namespace PhpBench\Attributes;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class Format
{
    /**
     * @var string
     */
    public $format;

    public function __construct(string $format)
    {
        $this->format = $format;
    }
}
