<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Report\Cellular;

use PhpBench\Result\SuiteResult;
use PhpBench\Result\BenchmarkResult;
use PhpBench\Result\SubjectResult;
use DTL\Cellular\Workspace;

/**
 * Convert a test suite result into a Cellular workspace.
 */
class CellularConverter
{
    public static function suiteToWorkspace(SuiteResult $suite)
    {
        $workspace = Workspace::create();
        foreach ($suite->getBenchmarkResults() as $benchmark) {
            self::convertBenchmark($benchmark, $workspace);
        }

        return $workspace;
    }

    private static function convertBenchmark(BenchmarkResult $benchmark, $workspace)
    {
        foreach ($benchmark->getSubjectResults() as $subject) {
            self::convertSubject($subject, $benchmark, $workspace);
        }
    }

    private static function convertSubject(SubjectResult $subject, BenchmarkResult $benchmark, Workspace $workspace)
    {
        $table = $workspace->createAndAddTable(array('main'));
        $table->setTitle($benchmark->getClass() . '->' . $subject->getName());
        $table->setDescription($subject->getDescription());
        $table->setAttribute('class', $benchmark->getClass());
        $table->setAttribute('subject', $subject->getName());
        $table->setAttribute('description', $subject->getDescription());

        foreach ($subject->getIterationsResults() as $runIndex => $aggregateResult) {
            foreach ($aggregateResult->getIterationResults() as $iterationIndex => $iteration) {
                $stats = $iteration->getStatistics();
                $row = $table->createAndAddRow(array('main'));
                $row->set('run', $runIndex, array('#run'));
                $row->set('iter', $iterationIndex, array('#iter'));

                $row->set('params', json_encode($aggregateResult->getParameters()), array('#params'));

                $stat = $iteration->getStatistics();
                $row->set('time', $stat['time'], array('#time', 'aggregate', '.time'));
                $row->set('revs', $stats['revs'], array('#revs', 'aggregate', '.revs'));
                $row->set('memory', $stat['memory'], array('#memory', 'aggregate', '.memory'));
                $row->set('memory_diff', $stat['memory_diff'], array('#memory_diff', 'aggregate', '.memory', '.diff'));
                $row->set('memory_inc', $stat['memory_inc'], array('#memory_inc', 'aggregate', '.memory', 'inc'));
                $row->set('memory_diff_inc', $stat['memory_diff_inc'], array('#memory_diff_inc', 'aggregate', '.memory'));
            }
        }
    }
}
