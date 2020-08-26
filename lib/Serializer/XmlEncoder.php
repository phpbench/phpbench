<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace PhpBench\Serializer;

use PhpBench\Dom\Document;
use PhpBench\Dom\Element;
use PhpBench\Model\Benchmark;
use PhpBench\Model\ResolvedExecutor;
use PhpBench\Model\Subject;
use PhpBench\Model\Suite;
use PhpBench\Model\SuiteCollection;
use PhpBench\Model\Variant;
use PhpBench\PhpBench;
use PhpBench\Util\TimeUnit;

/**
 * Encodes the Suite object graph into an XML document.
 */
class XmlEncoder
{
    /**
     * Encode a Suite object into a XML document.
     *
     * @param SuiteCollection $suiteCollection
     *
     * @return Document
     */
    public function encode(SuiteCollection $suiteCollection)
    {
        $dom = new Document();

        $rootEl = $dom->createRoot('phpbench');
        $rootEl->setAttribute('version', PhpBench::VERSION);
        $rootEl->setAttributeNS(
            'http://www.w3.org/2000/xmlns/',
            'xmlns:xsi',
            'http://www.w3.org/2001/XMLSchema-instance'
        );

        foreach ($suiteCollection->getSuites() as $suite) {
            $suiteEl = $rootEl->appendElement('suite');
            $suiteEl->setAttribute('tag', $suite->getTag());

            // @deprecated context is deprecated and replaced by `tag`, to be
            //             removed in version 1.0
            $suiteEl->setAttribute('context', $suite->getTag());
            $suiteEl->setAttribute('date', $suite->getDate()->format('c'));
            $suiteEl->setAttribute('config-path', $suite->getConfigPath());
            $suiteEl->setAttribute('uuid', $suite->getUuid());

            $envEl = $suiteEl->appendElement('env');

            foreach ($suite->getEnvInformations() as $information) {
                $infoEl = $envEl->appendElement($information->getName());

                foreach ($information as $key => $value) {
                    $infoEl->setAttribute($key, $value);
                }
            }

            foreach ($suite->getBenchmarks() as $benchmark) {
                $this->processBenchmark($benchmark, $suiteEl);
            }
        }

        return $dom;
    }

    private function processBenchmark(Benchmark $benchmark, Element $suiteEl)
    {
        $benchmarkEl = $suiteEl->appendElement('benchmark');
        $benchmarkEl->setAttribute('class', $benchmark->getClass());

        foreach ($benchmark->getSubjects() as $subject) {
            $this->processSubject($subject, $benchmarkEl);
        }
    }

    private function processSubject(Subject $subject, Element $benchmarkEl)
    {
        $subjectEl = $benchmarkEl->appendElement('subject');
        $subjectEl->setAttribute('name', $subject->getName());

        $this->appendExecutor($subjectEl, $subject->getExecutor());

        foreach ($subject->getGroups() as $group) {
            $groupEl = $subjectEl->appendElement('group');
            $groupEl->setAttribute('name', $group);
        }

        foreach ($subject->getVariants() as $variant) {
            $this->processVariant($subject, $variant, $subjectEl);
        }
    }

    private function processVariant(Subject $subject, Variant $variant, Element $subjectEl)
    {
        $variantEl = $subjectEl->appendElement('variant');

        // TODO: These attributes should be on the subject, see
        // https://github.com/phpbench/phpbench/issues/307
        $variantEl->setAttribute('sleep', $subject->getSleep());
        $variantEl->setAttribute('output-time-unit', $subject->getOutputTimeUnit() ?: TimeUnit::MICROSECONDS);
        $variantEl->setAttribute('output-time-precision', $subject->getOutputTimePrecision());
        $variantEl->setAttribute('output-mode', $subject->getOutputMode() ?: TimeUnit::MODE_TIME);
        $variantEl->setAttribute('revs', (string) $variant->getRevolutions());
        $variantEl->setAttribute('warmup', (string) $variant->getWarmup());
        $variantEl->setAttribute('retry-threshold', $subject->getRetryThreshold());

        $parameterSetEl = $variantEl->appendElement('parameter-set');
        $parameterSetEl->setAttribute('name', $variant->getParameterSet()->getName());

        foreach ($variant->getParameterSet() as $name => $value) {
            $this->createParameter($parameterSetEl, $name, $value);
        }

        if ($variant->hasErrorStack()) {
            $errorsEl = $variantEl->appendElement('errors');

            foreach ($variant->getErrorStack() as $error) {
                $errorEl = $errorsEl->appendElement('error', $error->getMessage());
                $errorEl->setAttribute('exception-class', $error->getClass());
                $errorEl->setAttribute('code', $error->getCode());
                $errorEl->setAttribute('file', $error->getFile());
                $errorEl->setAttribute('line', $error->getLine());
            }

            return;
        }

        if ($variant->hasWarning()) {
            $warningsEl = $variantEl->appendElement('warnings');

            foreach ($variant->getWarnings() as $warning) {
                $warningEl = $warningsEl->appendElement('warning', $warning->getMessage());
            }
        }

        if ($variant->hasFailed()) {
            $failuresEl = $variantEl->appendElement('failures');

            foreach ($variant->getFailures() as $failure) {
                $failureEl = $failuresEl->appendElement('failure', $failure->getMessage());
            }
        }

        $resultClasses = [];

        foreach ($variant as $iteration) {
            $iterationEl = $variantEl->appendElement('iteration');

            foreach ($iteration->getResults() as $result) {

                // we need to store the class FQNs of the results for deserialization later.
                if (!isset($resultClasses[$result->getKey()])) {
                    $resultClasses[$result->getKey()] = get_class($result);
                }

                $prefix = $result->getKey();
                $metrics = $result->getMetrics();

                foreach ($metrics as $key => $value) {
                    $iterationEl->setAttribute(sprintf(
                        '%s-%s',
                        $prefix,
                        str_replace('_', '-', $key)
                    ), $value);
                }
            }
        }

        $statsEl = $variantEl->appendElement('stats');
        $this->buildStatsEl($variant, $statsEl);

        if ($variant->getBaseline()) {
            $baselineEl = $variantEl->appendElement('baseline-stats');
            $this->buildStatsEl($variant->getBaseline(), $baselineEl);
        }

        foreach ($resultClasses as $resultKey => $classFqn) {
            if ($subjectEl->queryOne('ancestor::suite/result[@key="' . $resultKey . '"]')) {
                continue;
            }

            $resultEl = $subjectEl->queryOne('ancestor::suite')->appendElement('result');
            $resultEl->setAttribute('key', $resultKey);
            $resultEl->setAttribute('class', $classFqn);
        }
    }

    private function createParameter($parentEl, $name, $value)
    {
        $parameterEl = $parentEl->appendElement('parameter');
        $parameterEl->setAttribute('name', $name);

        if (is_array($value)) {
            $parameterEl->setAttribute('type', 'collection');

            foreach ($value as $key => $element) {
                $this->createParameter($parameterEl, $key, $element);
            }

            return $parameterEl;
        }

        if (is_null($value)) {
            $parameterEl->setAttribute('xsi:nil', 'true');

            return $parameterEl;
        }

        if (is_scalar($value)) {
            $parameterEl->setAttribute('value', $value);

            return $parameterEl;
        }

        throw new \InvalidArgumentException(sprintf(
            'Parameters must be either scalars or arrays, got: %s',
            is_object($value) ? get_class($value) : gettype($value)
        ));
    }

    private function appendExecutor(Element $subjectEl, ResolvedExecutor $executor = null): void
    {
        if (null === $executor) {
            return;
        }

        $executorEl = $subjectEl->appendElement('executor');
        $executorEl->setAttribute('name', $executor->getName());
        $subjectEl->appendChild($executorEl);

        foreach ($executor->getConfig() as $key => $value) {
            $this->createParameter($executorEl, $key, $value);
        }
    }

    private function buildStatsEl(Variant $variant, Element $statsEl): void
    {
        $stats = $variant->getStats();
        $stats = iterator_to_array($stats);
        // ensure same order (for testing)
        ksort($stats);

        foreach ($stats as $statName => $statValue) {
            $statsEl->setAttribute($statName, $statValue);
        }
    }
}
