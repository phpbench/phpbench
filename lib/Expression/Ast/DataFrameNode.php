<?php

namespace PhpBench\Expression\Ast;

use PhpBench\Data\DataFrame;

class DataFrameNode extends PhpValue
{
    public function __construct(private readonly DataFrame $dataFrame)
    {
    }

    public function dataFrame(): DataFrame
    {
        return $this->dataFrame;
    }

    /**
     * {@inheritDoc}
     */
    public function value()
    {
        return $this->dataFrame->toRecords();
    }
}
