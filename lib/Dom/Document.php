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
 * Wrapper for the \DOMDocument class.
 */
class Document extends \DOMDocument implements XPathAware
{
    /**
     * @var XPath|null
     */
    private $xpath;

    /**
     * @param string $version
     * @param string $encoding
     */
    public function __construct($version = '1.0', $encoding = null)
    {
        if ($encoding) {
            parent::__construct($version, $encoding);
        } else {
            parent::__construct($version);
        }
        $this->registerNodeClass('DOMElement', 'PhpBench\Dom\Element');
    }

    /**
     * Create and return a root DOM element.
     *
     * @param string $name
     *
     */
    public function createRoot($name): Element
    {
        $element = $this->appendChild(new Element($name));
        assert($element instanceof Element);

        return $element;
    }

    /**
     * Return the XPath object bound to this document.
     *
     */
    public function xpath(): XPath
    {
        if ($this->xpath) {
            return $this->xpath;
        }

        $this->xpath = new XPath($this);

        return $this->xpath;
    }

    /**
     * @return DOMNodeList<DOMNode>
     */
    public function query($query, DOMNode $context = null): DOMNodeList
    {
        return $this->xpath()->query($query, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function queryOne($query, DOMNode $context = null): ?Element
    {
        return $this->xpath()->queryOne($query, $context);
    }

    /**
     * @return mixed
     */
    public function evaluate($expression, DOMNode $context = null)
    {
        return $this->xpath()->evaluate($expression, $context);
    }

    /**
     * Return a formatted string representation of the document.
     *
     */
    public function dump(): string
    {
        $this->formatOutput = true;
        $result = $this->saveXML();
        $this->formatOutput = false;

        if (false === $result) {
            throw new RuntimeException('Could not dump XML');
        }

        return $result;
    }

    public function duplicate(): Document
    {
        $dom = new self();

        if ($this->firstChild) {
            $firstChild = $dom->importNode($this->firstChild, true);
            $dom->appendChild($firstChild);
        }


        return $dom;
    }
}
