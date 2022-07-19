<?php

namespace PhpBench\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
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
