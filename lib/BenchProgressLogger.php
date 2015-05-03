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

interface BenchProgressLogger
{
    public function caseEnd(BenchCase $case);

    public function caseStart(BenchCase $case);

    public function subjectEnd(BenchSubject $case);

    public function subjectStart(BenchSubject $case);
}
