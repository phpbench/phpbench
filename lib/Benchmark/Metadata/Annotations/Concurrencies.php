<?php

namespace PhpBench\Benchmark\Metadata\Annotations;

/**
 * @Annotation
 * @Taget({"METHOD", "CLASS"})
 * @Attributes({
 *    @Attribute("value", required = true,  type = "array"),
 * })
 */
class Concurrencies extends ArrayAnnotation
{
    private $concurrencies;

    public function __construct($params)
    {
        parent::__construct($params);
        $this->concurrencies = (array) $params['value'];
    }

    public function getConcurrencies()
    {
        return $this->concurrencies;
    }
}
