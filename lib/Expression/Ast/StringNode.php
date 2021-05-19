<?php

namespace PhpBench\Expression\Ast;

final class StringNode extends PhpValue
{
    /**
     * @var string
     */
    private $string;

    public function __construct(string $string)
    {
        $this->string = $string;
    }

    /**
     * {@inheritDoc}
     */
    public function value()
    {
        return $this->string;
    }
}
