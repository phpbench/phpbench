<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\ProgressLogger;

use PhpBench\BenchCase;
use PhpBench\BenchProgressLogger;
use PhpBench\BenchSubject;
use Symfony\Component\Console\Output\OutputInterface;

class NullProgressLogger implements BenchProgressLogger
{
    public function caseStart(BenchCase $case)
    {
    }

    public function caseEnd(BenchCase $case)
    {
    }

    public function subjectStart(BenchSubject $subject)
    {
    }

    public function subjectEnd(BenchSubject $subject)
    {
    }
}

