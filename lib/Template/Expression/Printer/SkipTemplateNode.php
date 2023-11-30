<?php

namespace PhpBench\Template\Expression\Printer;

use PhpBench\Expression\Ast\Node;

class SkipTemplateNode extends Node
{
    public function __construct(private readonly Node $subject)
    {
    }

    public function subject(): Node
    {
        return $this->subject;
    }
}
