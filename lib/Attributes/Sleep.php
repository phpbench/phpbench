<?php

namespace PhpBench\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
final class Sleep
{
    /**
     * @var int
     */
    public $sleep;

    public function __construct(int $sleep)
    {
        $this->sleep = $sleep;
    }
}
