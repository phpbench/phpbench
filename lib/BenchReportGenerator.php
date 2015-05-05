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

interface BenchReportGenerator
{
    public function configure(OptionsResolver $options);

    public function generate(BenchCaseCollectionResult $collection, array $config);
}
