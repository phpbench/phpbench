<?php

namespace PhpBench\Attributes;

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
