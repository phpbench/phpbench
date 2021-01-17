<?php

namespace PhpBench\Tests\Util;

use PhpBench\Model\Iteration;
use PhpBench\Model\ResultInterface;
use PhpBench\Model\Variant;

class IterationBuilder
{
    /**
     * @var VariantBuilder
     */
    private $variant;

    /**
     * @var ResultInterface[]
     */
    private $results = [];

    public function __construct(VariantBuilder $variant)
    {
        $this->variant = $variant;
    }

    public function setResult(ResultInterface $result): self
    {
        $this->results[] = $result;

        return $this;
    }

    public function end(): VariantBuilder
    {
        return $this->variant;
    }

    public function build(Variant $variant): Iteration
    {
        return $variant->createIteration($this->results);
    }
}
