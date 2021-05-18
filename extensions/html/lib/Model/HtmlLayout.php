<?php

namespace PhpBench\Extensions\Html\Model;

use PhpBench\Report\Model\Reports;

class HtmlLayout
{
    /**
     * @var Reports
     */
    private $reports;

    /**
     * @var array
     */
    private $cssPaths;

    public function __construct(Reports $reports, array $cssPaths)
    {
        $this->reports = $reports;
        $this->cssPaths = $cssPaths;
    }

    public function reports(): Reports
    {
        return $this->reports;
    }

    public function cssPaths(): array
    {
        return $this->cssPaths;
    }
}
