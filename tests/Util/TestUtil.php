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

namespace PhpBench\Tests\Util;

use PhpBench\Environment\Information;
use PhpBench\Model\ParameterSet;
use PhpBench\Model\Result\MemoryResult;
use PhpBench\Model\Result\RejectionCountResult;
use PhpBench\Model\Result\TimeResult;
use PhpBench\Model\Suite;
use PhpBench\Model\SuiteCollection;
use PhpBench\Model\Variant;
use Prophecy\Prophecy\ObjectProphecy;
use RuntimeException;

/**
 * Utility class for configuring benchmarking prophecy objects.
 */
class TestUtil
{
    public static function configureSubjectMetadata(ObjectProphecy $subject, array $options = []): void
    {
        $options = array_merge([
            'iterations' => 1,
            'name' => 'benchFoo',
            'beforeMethods' => [],
            'afterMethods' => [],
            'parameterSets' => [[[]]],
            'groups' => [],
            'revs' => 1,
            'warmup' => 0,
            'notApplicable' => false,
            'skip' => false,
            'sleep' => 0,
            'paramProviders' => [],
            'outputTimeUnit' => 'microseconds',
            'outputMode' => 'time',
        ], $options);

        $subject->getIterations()->willReturn($options['iterations']);
        $subject->getSleep()->willReturn($options['sleep']);
        $subject->getName()->willReturn($options['name']);
        $subject->getBeforeMethods()->willReturn($options['beforeMethods']);
        $subject->getAfterMethods()->willReturn($options['afterMethods']);
        $subject->getParameterSetsCollection()->willReturn($options['parameterSets']);
        $subject->getGroups()->willReturn($options['groups']);
        $subject->getRevs()->willReturn($options['revs']);
        $subject->getSkip()->willReturn($options['skip']);
        $subject->getWarmup()->willReturn($options['warmup']);
        $subject->getParamProviders()->willReturn($options['paramProviders']);
        $subject->getOutputTimeUnit()->willReturn($options['outputTimeUnit']);
        $subject->getOutputMode()->willReturn($options['outputMode']);
    }

    public static function getVariant(): Variant
    {
        $variants = self::createSuite()->getVariants();
        $variant = reset($variants);

        if (!$variant) {
            throw new RuntimeException(sprintf(
                'Could not find a variant in test suite'
            ));
        }

        return $variant;
    }

    public static function configureBenchmarkMetadata(ObjectProphecy $benchmark, array $options = []): void
    {
        $options = array_merge([
            'class' => 'Benchmark',
            'beforeClassMethods' => [],
            'afterClassMethods' => [],
            'path' => 'example',
        ], $options);

        $benchmark->getClass()->willReturn($options['class']);
        $benchmark->getBeforeClassMethods()->willReturn($options['beforeClassMethods']);
        $benchmark->getAfterClassMethods()->willReturn($options['afterClassMethods']);
        $benchmark->getPath()->willReturn($options['path']);
        $benchmark->getExecutor()->willReturn(null);
    }

    /**
     * @param array<string,mixed> $options
     */
    public static function createSuite(array $options = [], $suiteIndex = null): Suite
    {
        $options = array_merge([
            'uuid' => $suiteIndex,
            'date' => '2016-02-06',
            'revs' => 5,
            'warmup' => 10,
            'sleep' => 1,
            'baseline' => null,
            'name' => 'test',
            'benchmarks' => ['TestBench'],
            'groups' => ['one', 'two', 'three'],
            'parameters' => [
                'param1' => 'value1',
            ],
            'subjects' => ['benchOne'],
            'env' => [],
            'output_time_unit' => 'microseconds',
            'output_time_precision' => 7,
            'output_mode' => 'time',
            'iterations' => [0, 10],
            'iterations_increase_per_subject' => 0,
        ], $options);

        $dateTime = new \DateTime($options['date']);
        $suite = new Suite(
            $options['name'],
            $dateTime,
            null,
            [],
            [],
            $options['uuid']
        );


        foreach ($options['benchmarks'] as $benchmarkClass) {
            $benchmark = $suite->createBenchmark($benchmarkClass);
            $timeOffset = 0;

            foreach ($options['subjects'] as $subjectName) {
                $subject = $benchmark->createSubject($subjectName);
                $subject->setSleep($options['sleep']);
                $subject->setGroups($options['groups']);
                $subject->setOutputTimeUnit($options['output_time_unit']);
                $subject->setOutputTimePrecision($options['output_time_precision']);
                $subject->setOutputMode($options['output_mode']);
                $variant = $subject->createVariant(ParameterSet::fromUnserializedValues('0', $options['parameters']), $options['revs'], $options['warmup']);

                foreach ($options['iterations'] as $time) {
                    $variant->createIteration(self::createResults($timeOffset + $time, 200, 0));
                }

                $timeOffset += $options['iterations_increase_per_subject'];

                $variant->computeStats();
            }
        }

        $informations = [];

        foreach ($options['env'] as $name => $information) {
            $informations[] = new Information($name, $information);
        }
        $suite->setEnvInformations($informations);

        if ($options['baseline']) {
            $baselineSuite = self::createSuite(array_merge([
                'name' => 'baseline',
            ], $options['baseline']));
            $suite->mergeBaselines(new SuiteCollection([$baselineSuite]));
        }

        return $suite;
    }

    /**
     * @var array<string,mixed>
     */
    public static function createCollection(array $suiteConfigs = []): SuiteCollection
    {
        $suites = [];

        foreach ($suiteConfigs as $suiteIndex => $suiteConfig) {
            $suites[] = self::createSuite($suiteConfig, $suiteIndex);
        }

        return new SuiteCollection($suites);
    }

    public static function createResults($time, $memory = 0)
    {
        return [
            new TimeResult($time),
            new MemoryResult($memory, $memory, $memory),
            new RejectionCountResult(0),
        ];
    }
}
