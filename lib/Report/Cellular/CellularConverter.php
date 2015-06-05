<?php

namespace PhpBench\Report\Cellular;

/**
 * Convert a test suite result into a Cellular workspace
 */
class CellularConverter
{
    public function suiteToWorkspace(SuiteResult $suite, array $groups)
    {
        $workspace = Workspace::create();
        foreach ($suite->getBenchmarkResults() as $benchmark) {
            $this->convertBenchmark($benchmark, $workspace, $groups);
        }

        return $workspace;
    }

    private function convertBenchmark(BenchmarkResult $benchmark, $workspace, array $groups)
    {
        foreach ($benchmark->getSubjectResults() as $subject) {
            if ($groups && 0 === count(array_intersect($groups, $subject->getGroups()))) {
                continue;
            }

            $this->convertSubject($subject, $benchmark, $workspace);
        }
    }

    private function convertSubject(SubjectResult $subject, BenchmarkResult $benchmark, Workspace $workspace)
    {
        $table = $workspace->createAndAddTable(array('main'));
        $table->setTitle($benchmark->getClass() . '->' . $subject->getName());
        $table->setDescription($subject->getDescription());

        foreach ($subject->getIterationsResults() as $runIndex => $aggregateResult) {
            foreach ($aggregateResult->getIterationResults() as $iteration) {
                $stats = $iteration->getStatistics();
                $row = $table->createAndAddRow(array('main'));
                $row->set('run', $runIndex);
                $row->set('iter', $iteration->getIndex(), array('main', 'iteration'));

                foreach ($aggregateResult->getParameters() as $paramName => $paramValue) {
                    $row->set($paramName, $paramValue, array('param'));
                }

                $row->set('time', $stat['time'], array('main', 'memory', 'inc'));
                $row->set('revs', $stats['revs']);
                $row->set('memory', $stat['memory'], array('main', 'memory'));
                $row->set('memory_diff', $stat['memory_diff'], array('main', 'memory', 'diff'));
                $row->set('memory_inc', $stat['memory_inc'], array('main', 'memory', 'inc'));
                $row->set('memory_diff_inc', $stat['memory_diff_inc'], array('main', 'memory', 'inc'));
            }
        }
    }
}
