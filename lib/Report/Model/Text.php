<?php

namespace PhpBench\Report\Model;

use PhpBench\Report\ComponentInterface;

class Text implements ComponentInterface
{
    public function __construct(private readonly string $text, private readonly ?string $title = null)
    {
    }

    public function title(): ?string
    {
        return $this->title;
    }

    public function text(): string
    {
        return $this->text;
    }
}
