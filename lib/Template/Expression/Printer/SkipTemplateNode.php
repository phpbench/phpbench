<?php

namespace PhpBench\Template\Expression\Printer;

use PhpBench\Expression\Ast\Node;

class SkipTemplateNode extends Node
{
    /**
     * @var Node
     */
    private $subject;

    public function __construct(Node $subject)
    {
        $this->subject = $subject;
    }

    public function subject(): Node
    {
        return $this->subject;
    }
}
