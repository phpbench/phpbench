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

use PhpBench\BenchCaseCollectionResult;
use PhpBench\BenchFinder;
use PhpBench\BenchRunner;
use PhpBench\BenchSubjectBuilder;
use Symfony\Component\Finder\Finder;
use Symfony\Component\OptionsResolver\OptionsResolver;
use PhpBench\Benchmark\SubjectBuilder;
use PhpBench\Benchmark\Runner;
use PhpBench\Benchmark\CollectionBuilder;
use PhpBench\Result\SuiteResult;

abstract class BaseReportGeneratorCase extends \PHPUnit_Framework_TestCase
{
    protected function getResults()
    {
        $finder = new Finder();
        $finder->in(__DIR__ . '/../benchmarks');

        $benchFinder = new CollectionBuilder($finder);
        $subjectBuilder = new SubjectBuilder();

        $benchRunner = new Runner(
            $benchFinder,
            $subjectBuilder
        );

        return $benchRunner->runAll();
    }

    protected function executeReport(SuiteResult $results, array $options)
    {
        $resolver = new OptionsResolver();
        $report = $this->getReport();
        $report->configure($resolver);
        $options = $resolver->resolve($options);
        $report->generate($results, $options);
    }
}
