<?php

namespace PhpBench\Report\Model;

use PhpBench\Report\ComponentGenerator\TableAggregate\GroupHelper;

final class TableColumnGroup
{
    /**
     * @var string
     */
    private $label;
    /**
     * @var int
     */
    private $size;

    public function __construct(string $label, int $size)
    {
        $this->label = $label;
        $this->size = $size;
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
