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

use DOMElement;
use PhpBench\Assertion\AssertionResult;
use PhpBench\Dom\Document;
use PhpBench\Dom\Element;
use PhpBench\Environment\Information;
use PhpBench\Model\Benchmark;
use PhpBench\Model\Error;
use PhpBench\Model\ParameterSet;
use PhpBench\Model\ResolvedExecutor;
use PhpBench\Model\Subject;
use PhpBench\Model\Suite;
use PhpBench\Model\SuiteCollection;
use PhpBench\Model\Variant;
use PhpBench\PhpBench;
use PhpBench\Registry\Config;

use function base64_decode;

/**
 * Encodes the Suite object graph into an XML document.
 */
class XmlDecoder
{
    /**
     * Decode a PHPBench XML document into a SuiteCollection.
     *
     */
    public function decode(Document $document): SuiteCollection
    {
        $suites = [];

        foreach ($document->query('//suite') as $suiteEl) {
            $suites[] = $this->processSuite($suiteEl);
        }

        return new SuiteCollection($suites);
    }

    /**
     * Return a SuiteCollection from a number of PHPBench xml files.
     *
     * @param string[] $files
     */
    public function decodeFiles(array $files): SuiteCollection
    {
        // combine into one document.
        //
        $suiteDocument = new Document('phpbench');
        $rootEl = $suiteDocument->createRoot('phpbench');

        foreach ($files as $file) {
            $fileDom = new Document();
            $fileDom->load($file);

            foreach ($fileDom->query('./suite') as $suiteEl) {
                $importedEl = $suiteDocument->importNode($suiteEl, true);
                $rootEl->appendChild($importedEl);
            }
        }

        return $this->decode($suiteDocument);
    }

    private function processSuite(Element $suiteEl): Suite
    {
        $suite = new Suite(
            $suiteEl->getAttribute('tag'),
            new \DateTime($suiteEl->getAttribute('date')),
            $suiteEl->getAttribute('config-path'),
            [],
            [],
            $suiteEl->getAttribute('uuid')
        );

        $informations = [];

        foreach ($suiteEl->query('./env/*') as $envEl) {
            assert($envEl instanceof Element);
            $name = $envEl->nodeName;
            $info = [];

            if ($envEl->childNodes->count()) {
                foreach ($envEl->childNodes as $value) {
                    if (!$value instanceof Element) {
                        continue;
                    }
                    $info[$value->getAttribute('name')] = $this->resolveEnvType($value->getAttribute('type'), $value->nodeValue);
                }
            } else { // legacy format
                foreach ($envEl->attributes as $iName => $valueAttr) {
                    $info[$iName] = $valueAttr->nodeValue;
                }
            }

            $informations[$name] = new Information($name, $info);
        }

        $resultClasses = [];

        foreach ($suiteEl->query('//result') as $resultEl) {
            assert($resultEl instanceof DOMElement);
            $class = $resultEl->getAttribute('class');

            if (!class_exists($class)) {
                throw new \RuntimeException(sprintf(
                    'XML file defines a non-existing result class "%s" - maybe you are missing an extension?',
                    $class
                ));
            }

            $resultClasses[$resultEl->getAttribute('key')] = $class;
        }

        $suite->setEnvInformations($informations);

        foreach ($suiteEl->query('./benchmark') as $benchmarkEl) {
            assert($benchmarkEl instanceof Element);
            $benchmark = $suite->createBenchmark(
                $benchmarkEl->getAttribute('class')
            );

            $this->processBenchmark($benchmark, $benchmarkEl, $resultClasses);
        }

        return $suite;
    }

    private function processBenchmark(Benchmark $benchmark, Element $benchmarkEl, array $resultClasses): void
    {
        foreach ($benchmarkEl->query('./subject') as $subjectEl) {
            $subject = $benchmark->createSubject($subjectEl->getAttribute('name'));
            $this->processSubject($subject, $subjectEl, $resultClasses);
        }
    }

    private function processSubject(Subject $subject, Element $subjectEl, array $resultClasses): void
    {
        $groups = [];

        foreach ($subjectEl->query('./group') as $groupEl) {
            $groups[] = $groupEl->getAttribute('name');
        }
        $subject->setGroups($groups);

        foreach ($subjectEl->query('./executor') as $executorEl) {
            $subject->setExecutor(ResolvedExecutor::fromNameAndConfig($executorEl->getAttribute('name'), new Config('asd', $this->getParameters($executorEl))));

            break;
        }

        // TODO: These attributes should be on the subject, see
        // https://github.com/phpbench/phpbench/issues/307
        foreach ($subjectEl->query('./variant') as $variantEl) {
            $subject->setSleep((int)$variantEl->getAttribute('sleep'));
            $subject->setOutputTimeUnit($variantEl->getAttribute('output-time-unit'));
            $subject->setOutputTimePrecision((int)$variantEl->getAttribute('output-time-precision'));
            $subject->setOutputMode($variantEl->getAttribute('output-mode'));
            $subject->setRetryThreshold((float)$variantEl->getAttribute('retry-threshold'));

            break;
        }

        foreach ($subjectEl->query('./variant') as $index => $variantEl) {
            $parameterSet = ParameterSet::fromUnserializedValues('0', []);

            foreach ($variantEl->query('./parameter-set') as $parameterSetEl) {
                $name = $parameterSetEl->getAttribute('name');
                $parameters = $this->getParameters($parameterSetEl);
                $parameterSet = ParameterSet::fromUnserializedValues($name, $parameters);

                break;
            }
            $stats = $this->getComputedStats($variantEl);
            $variant = $subject->createVariant($parameterSet, (int)$variantEl->getAttribute('revs'), (int)$variantEl->getAttribute('warmup'), $stats);
            $this->processVariant($variant, $variantEl, $resultClasses);
        }
    }

    private function getComputedStats(Element $element): array
    {
        $stats = [];

        foreach ($element->query('./stats') as $statsEl) {
            foreach ($statsEl->attributes as $key => $attribute) {
                $stats[$key] = (float)$attribute->nodeValue;
            }
        }

        return $stats;
    }

    private function getParameters(Element $element): array
    {
        $parameters = [];

        foreach ($element->query('./parameter') as $parameterEl) {
            $name = $parameterEl->getAttribute('name');

            if ($parameterEl->getAttribute('type') === XmlEncoder::PARAM_TYPE_COLLECTION) {
                $parameters[$name] = $this->getParameters($parameterEl);

                continue;
            }

            if ($parameterEl->getAttribute('type') === XmlEncoder::PARAM_TYPE_BINARY) {
                $parameters[$name] = base64_decode($parameterEl->nodeValue);

                continue;
            }

            if ($parameterEl->getAttribute('type') === XmlEncoder::PARAM_TYPE_SERIALIZED) {
                $parameters[$name] = unserialize(base64_decode($parameterEl->nodeValue));

                continue;
            }

            if ($parameterEl->getAttribute('xsi:nil') === 'true') {
                $parameters[$name] = null;

                continue;
            }

            $parameters[$name] = (function (DOMElement $element) {
                $value = $element->getAttribute('value');
                $type = $element->getAttribute('type');

                switch ($type) {
                    case 'integer':
                        return intval($value);
                    case 'double':
                        return floatval($value);
                }

                return $value;
            })($parameterEl);
        }

        return $parameters;
    }

    private function processVariant(Variant $variant, Element $variantEl, array $resultClasses): void
    {
        $errorEls = $variantEl->query('.//error');

        if ($errorEls->length) {
            $errors = [];

            foreach ($errorEls as $errorEl) {
                $error = new Error(
                    $errorEl->nodeValue,
                    $errorEl->getAttribute('exception-class'),
                    $errorEl->getAttribute('code'),
                    $errorEl->getAttribute('file'),
                    $errorEl->getAttribute('line'),
                    '' // we don't serialize the trace..
                );
                $errors[] = $error;
            }
            $variant->createErrorStack($errors);

            return;
        }

        $failureEls = $variantEl->query('.//failure');

        if ($failureEls->length) {
            $failures = [];

            foreach ($failureEls as $failureEl) {
                $variant->addAssertionResult(AssertionResult::fail($failureEl->nodeValue));
            }
        }

        foreach ($variantEl->query('./iteration') as $iterationEl) {
            $results = [];

            foreach ($iterationEl->attributes as $attributeEl) {
                $name = $attributeEl->name;

                if (false === strpos($name, '-')) {
                    throw new \RuntimeException(sprintf(
                        'Expected attribute name to have a result key prefix, got "%s".',
                        $name
                    ));
                }

                $prefix = substr($name, 0, strpos($name, '-'));

                if (!isset($resultClasses[$prefix])) {
                    throw new \RuntimeException(sprintf(
                        'No result class was provided with key "%s" for attribute "%s"',
                        $prefix,
                        $name
                    ));
                }

                $suffix = substr($name, strpos($name, '-') + 1);
                $results[$prefix][str_replace('-', '_', $suffix)] = $attributeEl->value;
            }

            $iteration = $variant->createIteration();

            foreach ($results as $resultKey => $resultData) {
                $iteration->setResult(call_user_func_array([
                    $resultClasses[$resultKey],
                    'fromArray',
                ], [$resultData]));
            }
        }

        // TODO: Serialize statistics ..
        $variant->computeStats();
    }

    /**
     * @return mixed
     *
     * @param mixed $value
     */
    private function resolveEnvType(string $type, $value)
    {
        if ($type === 'boolean') {
            return (bool)$value;
        }

        return $value;
    }
}
