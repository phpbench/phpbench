<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Report\Generator;

use Symfony\Component\Console\Output\OutputInterface;
use PhpBench\Result\SuiteResult;
use Symfony\Component\OptionsResolver\OptionsResolver;
use PhpBench\OptionsResolver\OptionsResolver as BCOptionsResolver;

class ConsoleSimpleReportGenerator extends ConsoleTableReportGenerator
{
    public function configure(OptionsResolver $options)
    {
        parent::configure($options);
        $options->setDefault('cols', array('pid', 'revs', 'params', 'time', 'rps', 'memory_diff'));
    }
}
