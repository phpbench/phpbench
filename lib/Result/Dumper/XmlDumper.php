<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Result\Dumper;

use PhpBench\Result\SuiteResult;
use PhpBench\Result\BenchmarkResult;
use PhpBench\Result\SubjectResult;
use PhpBench\Result\IterationResult;
use PhpBench\PhpBench;
use PhpBench\Result\IterationsResult;

class XmlDumper
{
    public function dump($result)
    {
        $dom = new \DOMDocument('1.0');
        $dom->formatOutput = true;
        $rootEl = $dom->createElement('phpbench');
        $rootEl->setAttribute('version', PhpBench::VERSION);
        $rootEl->setAttribute('date', date('c'));
        $dom->appendChild($rootEl);

        $childEl = null;

        if (get_class($result) === 'PhpBench\Result\SuiteResult') {
            $childEl = $this->dumpSuite($result, $dom);
            $rootEl->appendChild($childEl);
        }

        if (null === $rootEl) {
            throw new InvalidArgumentException(sprintf(
                'Do not know how to dump result of type "%s"', get_class($result)
            ));
        }

        return $dom->saveXml();
    }

    private function dumpSuite(SuiteResult $suiteResult, $dom)
    {
        $suiteEl = $dom->createElement('suite');

        foreach ($suiteResult->getBenchmarkResults() as $benchmarkResult) {
            $benchmarkEl = $this->dumpBenchmark($benchmarkResult, $dom);
            $suiteEl->appendChild($benchmarkEl);
        }

        return $suiteEl;
    }

    private function dumpBenchmark(BenchmarkResult $benchmarkResult, $dom)
    {
        $benchmarkEl = $dom->createElement('benchmark');
        $benchmarkEl->setAttribute('class', $benchmarkResult->getClass());

        foreach ($benchmarkResult->getSubjectResults() as $subjectResult) {
            $subjectEl = $this->dumpSubject($subjectResult, $dom);
            $benchmarkEl->appendChild($subjectEl);
        }

        return $benchmarkEl;
    }

    private function dumpSubject(SubjectResult $subjectResult, $dom)
    {
        $subjectEl = $dom->createElement('subject');
        $subjectEl->setAttribute('name', $subjectResult->getName());
        $subjectEl->setAttribute('description', $subjectResult->getDescription());

        foreach ($subjectResult->getIterationsResults() as $iterationsResults) {
            $iterationsResultsEl = $this->dumpIterations($iterationsResults, $dom);
            $subjectEl->appendChild($iterationsResultsEl);
        }

        return $subjectEl;
    }

    private function dumpIterations(IterationsResult $iterationsResults, $dom)
    {
        $iterationsEl = $dom->createElement('iterations');
        foreach ($iterationsResults->getParameters() as $key => $value) {
            $parameterEl = $dom->createElement('parameter');
            $parameterEl->setAttribute($key, $value);
            $iterationsEl->appendChild($parameterEl);
        }

        foreach ($iterationsResults->getIterationResults() as $iterationResult) {
            $iterationResultEl = $this->dumpIteration($iterationResult, $dom);
            $iterationsEl->appendChild($iterationResultEl);
        }

        return $iterationsEl;
    }

    private function dumpIteration(IterationResult $iteration, $dom)
    {
        $iterationEl = $dom->createElement('iteration');

        foreach ($iteration->getStatistics() as $key => $value) {
            $iterationEl->setAttribute(
                $key,
                $value
            );
        }

        return $iterationEl;
    }
}
