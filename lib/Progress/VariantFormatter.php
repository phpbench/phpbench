<?php

namespace PhpBench\Progress;

use PhpBench\Model\Variant;

interface VariantFormatter
{
    public function formatVariant(Variant $variant): string;
}
