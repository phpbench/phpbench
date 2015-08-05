<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Report\Dom;

require_once 'xpath_functions.php';

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
            'PhpBench\Report\Dom\functions\parameters_to_json',
            'PhpBench\Report\Dom\functions\class_name',
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function evaluate($expr, \DOMNode $context = null, $registerNodeNs = null)
    {
        if (!is_scalar($expr)) {
            throw new \InvalidArgumentException(sprintf(
                __METHOD__ . ' must be passed a scalar XPath expression, got: %s',
                print_r($expr, true)
            ));
        }

        $expr = preg_replace(
            '{php:bench\(\'([a-z]+)}',
            'php:function(\'PhpBench\\Report\\Dom\\functions\\\$1',
            $expr
        );

        return parent::evaluate($expr, $context, $registerNodeNs);
    }
}
