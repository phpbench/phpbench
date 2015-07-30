<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Result\Loader;

use PhpBench\Result\SuiteResult;
use PhpBench\Result\IterationResult;
use PhpBench\Result\SubjectResult;
use PhpBench\Result\BenchmarkResult;
use PhpBench\Result\IterationsResult;

class XmlLoader
{
    public function load($xml)
    {
        $dom = new \DOMDocument('1.0');
        $success = @$dom->loadXml($xml);

        if (false === $success) {
            throw new \RuntimeException(sprintf(
                'Could not decode XML document: %s',
                $xml
            ));
        }
        $xpath = new \DOMXpath($dom);

        $benchmarkResults = $this->getBenchmarkResults($xpath);

        $suite = new SuiteResult($benchmarkResults);

        return $suite;
    }

    private function getBenchmarkResults($xpath)
    {
        $benchmarkResults = array();
        foreach ($xpath->query('//suite/benchmark') as $benchmarkEl) {
            $class = $benchmarkEl->getAttribute('class');
            $subjectResults = $this->getSubjectResults($xpath, $benchmarkEl);
            $benchmarkResults[] = new BenchmarkResult($class, $subjectResults);
        }

        return $benchmarkResults;
    }

    private function getSubjectResults(\DOMXpath $xpath, \DOMElement $benchmarkEl)
    {
        $subjectResults = array();
        foreach ($xpath->query('./subject', $benchmarkEl) as $subjectEl) {
            $identifier = $subjectEl->getAttribute('identifier');
            $name = $subjectEl->getAttribute('name');
            $iterationsResults = $this->getIterationsResults($xpath, $subjectEl);
            $groups = $this->getGroups($xpath, $subjectEl);
            $parameters = $this->getParametersForNode($xpath, $subjectEl);
            $subjectResults[] = new SubjectResult($identifier, $name, $groups, $parameters, $iterationsResults);
        }

        return $subjectResults;
    }

    private function getGroups(\DOMXPath $xpath, \DOMElement $subjectEl)
    {
        $groups = array();
        foreach ($xpath->query('./group', $subjectEl) as $groupEl) {
            $groups[] = $groupEl->getAttribute('name');
        }

        return $groups;
    }

    private function getIterationsResults(\DOMXPath $xpath, \DOMElement $subjectEl)
    {
        $iterationsResults = array();
        foreach ($xpath->query('./iterations', $subjectEl) as $iterationsEl) {
            $iterationResults = $this->getIterationResults($xpath, $iterationsEl);
            $iterationsResults[] = new IterationsResult($iterationResults);
        }

        return $iterationsResults;
    }

    private function getParametersForNode(\DOMXpath $xpath, \DOMNode $node)
    {
        $parameters = array();
        foreach ($xpath->query('./parameter', $node) as $parameterEl) {
            if (1 == $parameterEl->getAttribute('multiple')) {
                $parameters[$parameterEl->getAttribute('name')] = $this->getParametersForNode($xpath, $parameterEl);
            } else {
                $parameters[$parameterEl->getAttribute('name')] = $parameterEl->getAttribute('value');
            }
        }

        return $parameters;
    }

    private function getIterationResults(\DOMXPath $xpath, \DOMElement $iterationsEl)
    {
        $iterationResults = array();
        foreach ($xpath->query('./iteration', $iterationsEl) as $iterationEl) {
            $statistics = array();
            foreach ($iterationEl->attributes as $attrName => $attrNode) {
                $statistics[$attrName] = $attrNode->value;
            }
            $iterationResults[] = new IterationResult($statistics);
        }

        return $iterationResults;
    }
}
