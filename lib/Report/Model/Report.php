<?php

namespace PhpBench\Report\Model;

use PhpBench\Report\ComponentInterface;

final class Report implements ComponentInterface
{
    /**
     * @internal Use the ReportBuilder
     *
     * @param ComponentInterface[] $objects
     * @param string[] $tabLabels
     */
    public function __construct(private readonly array $objects, private readonly ?string $title, private readonly bool $tabbed = false, private readonly ?string $description = null, private readonly array $tabLabels = [])
    {
    }

    /**
     * @deprecated to be removed in 2.0. Use ReportBuilder
     *
     * @param ComponentInterface[] $objects
     */
    public static function fromTables(array $objects, ?string $title = null, ?string $description = null): self
    {
        return new self($objects, $title, false, $description);
    }

    /**
     * @deprecated to be removed in 2.0. Use ReportBuilder
     */
    public static function fromTable(ComponentInterface $object, ?string $title = null, ?string $description = null): self
    {
        return new self([$object], $title, false, $description);
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
     * @return object[]
     */
    public function objects(): array
    {
        return $this->objects;
    }

    /**
     * @deprecated use objects() to be removed in 2.0
     *
     * @return Table[]
     */
    public function tables(): array
    {
        return array_filter($this->objects, function (object $object) {
            return $object instanceof Table;
        });
    }

    public function tabbed(): bool
    {
        return $this->tabbed;
    }

    /**
     * @return string[]
     */
    public function tabLabels(): array
    {
        $reportTitles = array_map(function (ComponentInterface $component) {
            return $component->title();
        }, $this->objects);

        return $this->tabLabels + $reportTitles;
    }
}
