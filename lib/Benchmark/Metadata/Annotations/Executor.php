<?php

namespace PhpBench\Benchmark\Metadata\Annotations;

/**
 * @Annotation
 * @Taget({"METHOD", "CLASS"})
 * @Attributes({
 *    @Attribute("value", required = true,  type = "string"),
 * })
 */
class Executor
{
    private $executor;

    public function __construct($attrs)
    {
        $this->executor = (string) $attrs['value'];
    }

    public function getExecutor()
    {
        return $this->executor;
    }
}
