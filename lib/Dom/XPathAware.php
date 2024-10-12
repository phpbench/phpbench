<?php

/*
 * This file is part of the PhpBench DOM  package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Dom;

use DOMNode;
use DOMNodeList;

interface XPathAware
{
    /**
     * Perform an xpath query on this document, optionally with
     * the given context node.
     *
     * If this interface is applied to an Element, then the element
     * should be used as the context if no context is given.
     *
     * @param string   $query
     * @param \DOMNode $context
     *
     * @return DOMNodeList<DOMNode>
     */
    public function query($query, DOMNode $context = null);

    /**
     * As with XPathAware::query but return a single node or NULL if no node was found.
     *
     * @param string   $query
     * @param \DOMNode $context
     *
     * @return Element|null
     */
    public function queryOne($query, DOMNode $context = null);

    /**
     * Evaluate an XPath expression on this document, optionally
     * with the given context node.
     *
     * If this interface is applied to an Element, then the element
     * should be used as the context if no context is given.
     *
     * @param string   $expression
     * @param \DOMNode $context
     *
     * @return mixed
     */
    public function evaluate($expression, DOMNode $context = null);
}
