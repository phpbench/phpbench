<?php

namespace PhpBench\Report\Dom;

class PhpBenchXpath extends \DOMXpath
{
    public function __construct(\DOMDocument $dom)
    {
        parent::__construct($dom);
        $this->registerPhpFunctions(array(
            'pb_deviation',
        ));
    }
}

function pb_deviation($standardValue, $value)
{
    return Calculator::deviation($standardValue, $value);
}
