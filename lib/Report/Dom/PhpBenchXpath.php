<?php

namespace PhpBench\Report\Dom;

use PhpBench\Report\Calculator;

require_once('xpath_functions.php');

/**
 * This class registers some PHP functions which can be used in XPath
 * expressions.  It will also expand `php:bench` to the `php:function` call and
 * prepend the namespace.
 */
class PhpBenchXpath extends \DOMXpath
{
    public function __construct(\DOMDocument $dom)
    {
        parent::__construct($dom);
        $this->registerNamespace('php', 'http://php.net/xpath');
        $this->registerPhpFunctions(array(
            'PhpBench\Report\Dom\functions\sum',
            'PhpBench\Report\Dom\functions\avg',
            'PhpBench\Report\Dom\functions\deviation',
            'PhpBench\Report\Dom\functions\min',
            'PhpBench\Report\Dom\functions\max',
            'PhpBench\Report\Dom\functions\median',
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function evaluate($expr, \DOMNode $context = null, $registerNodeNs = null)
    {
        $expr = preg_replace(
            '{php:bench\(\'([a-z]+)}', 
            'php:function(\'PhpBench\\Report\\Dom\\functions\\\$1',
            $expr
        );

        return parent::evaluate($expr, $context, $registerNodeNs);
    }
}
