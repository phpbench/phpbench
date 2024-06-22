<?php

namespace PhpBench\Tests\Util;

use PhpBench\Model\Iteration;
use PhpBench\Model\ResultInterface;
use PhpBench\Model\Variant;

class IterationBuilder
{
    /**
     * @var ResultInterface[]
     */
    private array $results = [];

    public function __construct(private readonly VariantBuilder $variant)
    {
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
