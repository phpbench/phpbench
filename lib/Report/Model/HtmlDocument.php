<?php

namespace PhpBench\Report\Model;

final class HtmlDocument
{
    public function __construct(private readonly string $title, private readonly Reports $reports)
    {
    }

    public function reports(): Reports
    {
        return $this->reports;
    }

    public function title(): string
    {
        return $this->title;
    }
}
