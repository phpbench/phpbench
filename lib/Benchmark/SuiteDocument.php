<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Benchmark;

use PhpBench\Dom\Document;

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
     * Return the number of subjects.
     *
     * @return int
     */
    public function getNbSubjects()
    {
        return (int) $this->xpath()->evaluate('count(//subject)');
    }

    /**
     * Return the number of iterations.
     *
     * @return int
     */
    public function getNbIterations()
    {
        return (int) $this->xpath()->evaluate('count(//iteration)');
    }

    /**
     * Return the number of rejected iterations.
     *
     * @return int
     */
    public function getNbRejects()
    {
        return (int) $this->xpath()->evaluate('sum(//iteration/@rejection-count)');
    }
}
