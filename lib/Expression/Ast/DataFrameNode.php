<?php

namespace PhpBench\Expression\Ast;

use PhpBench\Data\DataFrame;

class DataFrameNode extends PhpValue
{
    /**
     * @var DataFrame
     */
    private $dataFrame;

    public function __construct(DataFrame $dataFrame)
    {
        $this->dataFrame = $dataFrame;
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
