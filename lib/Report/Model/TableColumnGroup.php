<?php

namespace PhpBench\Report\Model;

use PhpBench\Report\ComponentGenerator\TableAggregate\GroupHelper;

final class TableColumnGroup
{
    public function __construct(private readonly string $label, private readonly int $size)
    {
    }

    public function size(): int
    {
        return $this->size;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function isDefault(): bool
    {
        return $this->label === GroupHelper::DEFAULT_GROUP_NAME;
    }
}
