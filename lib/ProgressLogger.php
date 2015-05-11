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

use PhpBench\Benchmark;
use PhpBench\Benchmark\Subject;

interface ProgressLogger
{
    public function benchmarkEnd(Benchmark $case);

    public function benchmarkStart(Benchmark $case);

    public function subjectEnd(Subject $case);

    public function subjectStart(Subject $case);
}
