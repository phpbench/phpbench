<?php

namespace PhpBench\Benchmark\Metadata\Annotations;

/**
 * @Annotation
 *
 * @Taget({"METHOD", "CLASS"})
 */
class Format
{
    private string $format;

    /**
     * @param array{value: string} $params
     */
    public function __construct($params)
    {
        $this->format = $params['value'];
    }

    public function getFormat(): string
    {
        return $this->format;
    }
}
