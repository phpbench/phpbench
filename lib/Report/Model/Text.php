<?php

namespace PhpBench\Report\Model;

use PhpBench\Report\ComponentInterface;

class Text implements ComponentInterface
{
    /**
     * @var string
     */
    public $text;

    public function __construct(string $text)
    {
        $this->text = $text;
    }
}
