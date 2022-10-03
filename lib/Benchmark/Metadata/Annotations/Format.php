<?php

namespace PhpBench\Benchmark\Metadata\Annotations;

/**
 * @Annotation
 *
 * @Taget({"METHOD", "CLASS"})
 */
class Format
{
    /**
     * @var string
     */
    private $format;

    public function __construct($params)
    {
        $this->format = $params['value'];
    }

    public function getFormat(): string
    {
        return $this->format;
    }
}
