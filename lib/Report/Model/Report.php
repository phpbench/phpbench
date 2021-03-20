<?php

namespace PhpBench\Report\Model;

final class Report
{
    /**
     * @var string|null
     */
    private $title;

    /**
     * @var string|null
     */
    private $description;

    /**
     * @var Table[]
     */
    private $tables;

    /**
     * @param Table[] $tables
     */
    public function __construct(array $tables, ?string $title, ?string $description)
    {
        $this->tables = $tables;
        $this->title = $title;
        $this->description = $description;
    }

    public function title(): ?string
    {
        return $this->title;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    /**
     * @return Table[]
     */
    public function tables(): array
    {
        return $this->tables;
    }
}
