<?php

namespace PhpBench\Model;

interface VariantEnhancedResultInterface
{
    /**
     * @return array<string,mixed>
     */
    public function getVariantEnhancedMetrics(Variant $variant): array;
}
