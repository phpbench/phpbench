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

use Symfony\Component\OptionsResolver\OptionsResolver;
use PhpBench\Result\SuiteResult;
use Symfony\Component\Console\Output\OutputInterface;

interface ReportGenerator
{
    public function configure(OptionsResolver $options);

    public function generate(SuiteResult $collection, array $config);
}
