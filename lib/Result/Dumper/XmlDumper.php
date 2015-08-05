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
    /**
     * Dump the result to an XML document and return the XML document.
     *
     * @param SuiteResult
     *
     * @return DOMDocument
     */
    public function dump(SuiteResult $result)
    {
        $dom = new \DOMDocument('1.0');
        $dom->formatOutput = true;
        $rootEl = $dom->createElement('phpbench');
        $rootEl->setAttribute('version', PhpBench::VERSION);
        $rootEl->setAttribute('date', date('c'));
        $dom->appendChild($rootEl);

        $childEl = $this->dumpSuite($result, $dom);
        $rootEl->appendChild($childEl);

        return $dom;
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
        $subjectEl->setAttribute('identifier', $subjectResult->getIdentifier());
        $subjectEl->setAttribute('name', $subjectResult->getName());

        $parameters = $subjectResult->getParameters();
        $this->appendParameters($subjectEl, $parameters);

        foreach ($subjectResult->getGroups() as $group) {
            $groupEl = $dom->createElement('group');
            $groupEl->setAttribute('name', $group);
            $subjectEl->appendChild($groupEl);
        }

        foreach ($subjectResult->getIterationsResults() as $iterationsResults) {
            $iterationsResultsEl = $this->dumpIterations($iterationsResults, $dom);
            $subjectEl->appendChild($iterationsResultsEl);
        }

        return $subjectEl;
    }

    private function dumpIterations(IterationsResult $iterationsResults, $dom)
    {
        $iterationsEl = $dom->createElement('iterations');

        foreach ($iterationsResults->getIterationResults() as $iterationResult) {
            $iterationResultEl = $this->dumpIteration($iterationResult, $dom);
            $iterationsEl->appendChild($iterationResultEl);
        }

        return $iterationsEl;
    }

    private function appendParameters($parentNode, $parameters)
    {
        foreach ($parameters as $key => $value) {
            $parameterEl = $parentNode->ownerDocument->createElement('parameter');
            $parameterEl->setAttribute('name', $key);

            if (is_array($value)) {
                $this->appendParameters($parameterEl, $value);
                $parameterEl->setAttribute('multiple', true);
            } elseif (is_scalar($value)) {
                $parameterEl->setAttribute('value', $value);
            } else {
                throw new \RuntimeException(sprintf(
                    'Cannot serialize parameter of type "%s" to XML',
                    gettype($value)
                ));
            }

            $parentNode->appendChild($parameterEl);
        }
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
