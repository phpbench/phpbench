<?php

namespace PhpBench\Expression\Ast;

class LabelNode extends PhpValue
{
    /**
     * @var PhpValue
     */
    private $label;

    public function __construct(PhpValue $label)
    {
        $this->label = $label;
    }

    /**
     * {@inheritDoc}
     */
    public function value()
    {
        return $this->label->value();
    }
}
