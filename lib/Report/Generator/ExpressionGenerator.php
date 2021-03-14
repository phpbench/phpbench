<?php

namespace PhpBench\Report\Generator;

use Generator;
use PhpBench\Dom\Document;
use PhpBench\Model\Benchmark;
use PhpBench\Model\Iteration;
use PhpBench\Model\ResultInterface;
use PhpBench\Model\Subject;
use PhpBench\Model\Suite;
use PhpBench\Model\SuiteCollection;
use PhpBench\Model\Variant;
use PhpBench\Registry\Config;
use PhpBench\Report\GeneratorInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use function array_combine;
use function array_reduce;

class ExpressionGenerator implements GeneratorInterface
{
    /**
     * {@inheritDoc}
     */
    public function configure(OptionsResolver $options): void
    {
        $options->setDefaults([
            'title' => null,
            'description' => null,
            'cols' => [
                'benchmark' => 'benchmark.class',
                'subject' => 'subject.name',
                'tag' => 'suite.tag',
                'groups' => 'join(", ", subject.groups)',
                'params' => 'json_encode(variant.parameters)',
                'revs' => 'variant.revs',
                'its' => 'variant.iterations',
                'mem_peak' => 'max(variant.mem.peak) as bytes)',
                'best' => 'min(variant.time.avg) as time)',
                'mode' => 'mode(variant.time.avg) as time)',
                'worst' => 'max(variant.time.avg) as time)',
                'rstdev' => 'rstdev(variant.time.avg) ~ "%"',
            ],
            'aggregate' => ['benchmark.class', 'subject.name', 'variant.name' ],
            'break' => ['tag', 'suite', 'date', 'stime'],
        ]);

        $options->setAllowedTypes('title', ['null', 'string']);
        $options->setAllowedTypes('description', ['null', 'string']);
        $options->setAllowedTypes('cols', 'array');
        $options->setAllowedTypes('aggregate', 'array');
        $options->setAllowedTypes('break', 'array');
    }

    /**
     * {@inheritDoc}
     */
    public function generate(SuiteCollection $collection, Config $config): Document
    {
        $data = iterator_to_array($this->reportData($collection));
        $aggregated = $this->aggregate($data, $config['aggregate']);
    }

    /**
     * @return Generator<array<string, mixed>>
     */
    private function reportData(SuiteCollection $collection): Generator
    {
        foreach ($collection as $suite) {
            assert($suite instanceof Suite);
            foreach ($suite->getSubjects() as $subject) {
                foreach ($subject->getVariants() as $variant) {
                    foreach ($variant->getIterations() as $iteration) {
                        yield array_merge([
                            'benchmark.class' => $subject->getBenchmark()->getClass(),
                            'subject.name' => $subject->getName(),
                            'groups' => $subject->getGroups(),
                            'variant.name' => $variant->getParameterSet()->getName(),
                            'variant.params' => $variant->getParameterSet()->getArrayCopy(),
                            'variant.revs' => $variant->getRevolutions(),
                            'variant.iterations' => count($variant->getIterations()),
                            'suite.tag' => $suite->getTag()->__toString(),
                            'suite.date' => $suite->getDate()->format('c')
                        ], $this->resultData($iteration));
                    }
                }
            }
        }
    }

    /**
     * @return array<string,mixed>
     */
    private function resultData(Iteration $iteration): array
    {
        $data = [];
        foreach ($iteration->getResults() as $result) {
            foreach ($result->getMetrics() as $key => $value) {
                $data[sprintf('result.%s.%s', $result->getKey(), $key)] = $value;
            }
        }

        return $data;
    }

    /**
     * @param array<string.mixed> $data
     * @param string[] $aggregateCols
     *
     * @return array<string,mixed>
     */
    private function aggregate(array $data, array $aggregateCols): array
    {
        $aggregated = [];
        foreach ($data as $row) {
            $hash = implode('-', array_map(function (string $key) use ($row) {
                return $row[$key];
            }, $aggregateCols));

            $aggregated[$hash] = (function () use ($row, $hash, $aggregated) {
                if (!isset($aggregated[$hash])) {
                    return $row;
                }

                return array_combine(array_keys($aggregated[$hash]), array_map(function ($aggValue, $value) {
                    return array_merge((array)$aggValue, (array)$value);
                }, $aggregated[$hash], $row));
            })();
        }

        return $aggregated;
    }
}
