<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Functional\ReportGenerator;

use PhpBench\Result\SuiteResult;
use Symfony\Component\Console\Output\NullOutput;
use PhpBench\Result\Loader\XmlLoader;
use PhpBench\OptionsResolver\OptionsResolver;

abstract class BaseReportGeneratorCase extends \PHPUnit_Framework_TestCase
{
    protected function getResults()
    {
        $xmlLoader = new XmlLoader();

        return $xmlLoader->load(file_get_contents(__DIR__ . '/../report.xml'));
    }

    protected function executeReport(SuiteResult $results, array $options)
    {
        $resolver = new OptionsResolver();
        $report = $this->getReport();
        $report->configure($resolver);
        $options = $resolver->resolve($options);
        $output = new NullOutput();
        $report->generate($results, $output, $options);
    }
}
