<?php

namespace PhpBench\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class Assert
{
    /**
     * @var string
     */
    public $expression;

    public function __construct(string $expression)
    {
        $this->expression = $expression;
    }
}
