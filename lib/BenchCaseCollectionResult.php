<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench;

class BenchCaseCollectionResult
{
    private $caseCollection;
    private $caseResults;

    public function __construct(BenchCaseCollection $caseCollection, array $caseResults)
    {
        $this->caseResults = $caseResults;
        $this->caseCollection = $caseCollection;
    }

    public function getCaseResults()
    {
        return $this->caseResults;
    }

    public function getCaseCollection()
    {
        return $this->caseCollection;
    }
}
