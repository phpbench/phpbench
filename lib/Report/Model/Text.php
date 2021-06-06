<?php

namespace PhpBench\Report\Model;

use PhpBench\Report\ComponentInterface;

class Text implements ComponentInterface
{
    /**
     * @var string
     */
    private $text;

    /**
     * @var string|null
     */
    private $title;

    public function __construct(string $text, ?string $title = null)
    {
        $this->text = $text;
        $this->title = $title;
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
