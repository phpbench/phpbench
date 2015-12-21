<?php

namespace PhpBench\Benchmark\Metadata\Annotations;

/**
 * @Annotation
 * @Taget({"METHOD", "CLASS"})
 * @Attributes({
 *    @Attribute("value", required = true, type="string")
 * })
 */
class OutputMode
{
    private $mode;

    public function __construct($params)
    {
        $this->mode = $params['value'];
    }

    public function getMode()
    {
        return $this->mode;
    }
}
