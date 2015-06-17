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
        $options->setDefaults(array(
            'style' => 'horizontal',
            'subject_meta' => true,
        ));
        $options->setBCAllowedValues(array(
            'style' => array('vertical', 'horizontal'),
        ));
    }

    public function generate(SuiteResult $suite, OutputInterface $output, array $options)
    {
        $resolver = new BCOptionsResolver();
        parent::configure($resolver);
        $options = array(
            'cols' => array('pid', 'revs', 'params', 'time', 'rps'),
        );
        $options = $resolver->resolve($options);

        return parent::generate($suite, $output, $options);
    }
}
