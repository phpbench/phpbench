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
use RuntimeException;

/**
 * Wrapper for the \DOMXPath class.
 */
class XPath extends \DOMXPath
{
    /**
     * {@inheritdoc}
     *
     * @param mixed $contextnode
     * @param bool $registerNodeNS
     */
    #[\ReturnTypeWillChange]
    public function evaluate($expression, $contextnode = null, $registerNodeNS = true)
    {
        $result = $this->execute('evaluate', 'expression', $expression, $contextnode, $registerNodeNS);

        return $result;
    }

    /**
     * @param bool $registerNodeNS
     * @param mixed $contextnode
     *
     * @return DOMNodeList<DOMNode>
     */
    #[\ReturnTypeWillChange]
    public function query($expression, $contextnode = null, $registerNodeNS = true): DOMNodeList
    {
        $list = $this->execute('query', 'query', $expression, $contextnode, $registerNodeNS);

        if (!$list instanceof DOMNodeList) {
            throw new RuntimeException(sprintf('Expected XPAth expression to return DOMNodeList, got "%s"', is_object($list) ? get_class($list) : gettype($list)));
        }

        return $list;
    }

    public function queryOne(string $expr, DOMNode $contextEl = null, bool $registerNodeNs = false): ?Element
    {
        $nodeList = $this->query($expr, $contextEl, $registerNodeNs);

        if (0 === $nodeList->length) {
            return null;
        }

        $node = $nodeList->item(0);

        if (!$node instanceof Element) {
            throw new RuntimeException(sprintf(
                'Expected "%s" but got "%s"',
                Element::class,
                $node ? get_class($node) : gettype($node)
            ));
        }

        return $node;
    }

    /**
     * Execute the given xpath method and cactch any errors.
     *
     * @param mixed $contextEl
     *
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    private function execute(string $method, string $context, string $query, $contextEl = null, bool $registerNodeNs = false)
    {
        libxml_use_internal_errors(true);

        $value = @parent::$method($query, $contextEl, $registerNodeNs);

        $xmlErrors = libxml_get_errors();

        if ($xmlErrors) {
            $errors = [];

            foreach ($xmlErrors as $xmlError) {
                $errors[] = sprintf('[%s] %s', $xmlError->code, $xmlError->message);
            }
            libxml_clear_errors();

            throw new Exception\InvalidQueryException(sprintf(
                'Errors encountered when evaluating XPath %s "%s": %s%s',
                $context,
                $query,
                PHP_EOL,
                implode(PHP_EOL, $errors)
            ));
        }

        libxml_use_internal_errors(false);

        return $value;
    }
}
