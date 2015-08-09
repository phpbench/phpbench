<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Benchmark;

/**
 * DOMDocument implementation for containing the benchmark suite
 * results.
 */
class SuiteDocument extends \DOMDocument
{
    public function __construct()
    {
        parent::__construct('1.0');
        $this->formatOutput = true;
    }

    public function xpath()
    {
        return new \DOMXpath($this);
    }

    public function getNbSubjects()
    {
        return (int) $this->xpath()->evaluate('count(//subject)');
    }

    public function getNbIterations()
    {
        return (int) $this->xpath()->evaluate('count(//iteration)');
    }
}
