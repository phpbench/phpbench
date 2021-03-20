<?php

namespace PhpBench\Report\Model;

use PhpBench\Expression\Ast\Node;

final class TableRow
{
    /**
     * @var array
     */
    private $cells;

    /**
     * @param Node[] $cells
     */
    private function __construct(array $cells)
    {
        $this->cells = $cells;
    }

    public static function fromArray(array $row): self
    {
        return new self(array_map(function (Node $node) {
            return $node;
        }, $row));
    }
}
