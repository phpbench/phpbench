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

class BenchCaseResult
{
    private $case;
    private $subjectResults;

    public function __construct(BenchCase $case, $subjectResults)
    {
        $this->case = $case;
        $this->subjectResults = $subjectResults;
    }

    public function getCase()
    {
        return $this->case;
    }

    public function getSubjectResults()
    {
        return $this->subjectResults;
    }
}
