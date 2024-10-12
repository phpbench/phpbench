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

/**
 * Wrapper for the \DOMElement class.
 */
class Element extends \DOMElement implements XPathAware
{
    /**
     * Create and append a text-node with the given name and value.
     */
    public function appendTextNode(string $name, ?string $value): Element
    {
        $el = new self($name);
        $element = $this->appendChild($el);
        assert($element instanceof Element);

        $element->appendChild(
            $this->owner()->createTextNode($value ?? '')
        );

        return $element;
    }

    /**
     * Create and append an element with the given name and optionally given value.
     *
     * Note: The value will not be escaped. Use DOMDocument::createTextNode() to create a text node with escaping support.
     */
    public function appendElement(string $name, ?string $value = null): Element
    {
        $element = $this->appendChild(new self($name, $value));
        assert($element instanceof Element);

        return $element;
    }

    /**
     * @return DOMNodeList<DOMNode>
     */
    public function query($xpath, DOMNode $context = null): DOMNodeList
    {
        return $this->owner()->xpath()->query($xpath, $context ?: $this);
    }

    public function queryOne($xpath, DOMNode $context = null): ?Element
    {
        return $this->owner()->xpath()->queryOne($xpath, $context ?: $this);
    }

    /**
     * @return mixed
     */
    public function evaluate($expression, DOMNode $context = null)
    {
        return $this->owner()->xpath()->evaluate($expression, $context ?: $this);
    }

    /**
     * Dump the current node
     */
    public function dump(): string
    {
        $document = new Document();
        $document->appendChild($document->importNode($this, true));

        return $document->dump();
    }

    private function owner(): Document
    {
        $owner = $this->ownerDocument;
        assert($owner instanceof Document);

        return $owner;
    }
}
