<?php

namespace PhpBench\Report\Model;

final class HtmlDocument
{
    /**
     * @var string
     */
    private $title;

    /**
     * @var Reports
     */
    private $reports;

    public function __construct(string $title, Reports $reports)
    {
        $this->title = $title;
        $this->reports = $reports;
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
