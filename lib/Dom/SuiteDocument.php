<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Dom;

/**
 * DOMDocument implementation for containing the benchmark suite
 * results.
 */
class SuiteDocument extends Document
{
    /**
     * Return a clone of the document>.
     *
     * @return SuiteDocument
     */
    public function duplicate()
    {
        $document = new self();
        $node = $document->importNode($this->firstChild, true);
        $document->appendChild($node);

        return $document;
    }

    /**
     * Append the suites container in another document to this document.
     *
     * @param SuiteDocument $suiteDocument
     * @param string $defaultName
     */
    public function appendSuiteDocument(SuiteDocument $suiteDocument, $defaultName = null)
    {
        $aggregateEl = $this->evaluate('/phpbench')->item(0);

        if (null === $aggregateEl) {
            throw new \InvalidArgumentException(
                'Suite document must have root element "phpbench" before appending other suites'
            );
        }

        foreach ($suiteDocument->xpath()->query('//suite') as $suiteEl) {
            $suiteEl = $this->importNode($suiteEl, true);

            if (!$suiteEl->getAttribute('context')) {
                $suiteEl->setAttribute('context', $defaultName);
            }

            $aggregateEl->appendChild($suiteEl);
        }
    }
}
