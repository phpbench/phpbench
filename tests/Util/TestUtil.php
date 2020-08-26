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
use Prophecy\Prophecy\ObjectProphecy;

/**
 * Utility class for configuring benchmarking prophecy objects.
 */
class TestUtil
{
    public static function configureSubjectMetadata(ObjectProphecy $subject, array $options = [])
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
        $subject->getParameterSets()->willReturn($options['parameterSets']);
        $subject->getGroups()->willReturn($options['groups']);
        $subject->getRevs()->willReturn($options['revs']);
        $subject->getSkip()->willReturn($options['skip']);
        $subject->getWarmup()->willReturn($options['warmup']);
        $subject->getParamProviders()->willReturn($options['paramProviders']);
        $subject->getOutputTimeUnit()->willReturn($options['outputTimeUnit']);
        $subject->getOutputMode()->willReturn($options['outputMode']);
    }

    public static function configureBenchmarkMetadata(ObjectProphecy $benchmark, array $options = [])
    {
        $options = array_merge([
            'class' => 'Benchmark',
            'beforeClassMethods' => [],
            'afterClassMethods' => [],
            'path' => null,
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
            'basetime' => 10,
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

            $baseTime = $options['basetime'];

            foreach ($options['subjects'] as $subjectName) {
                $subject = $benchmark->createSubject($subjectName);
                $subject->setSleep($options['sleep']);
                $subject->setGroups($options['groups']);
                $subject->setOutputTimeUnit($options['output_time_unit']);
                $subject->setOutputTimePrecision($options['output_time_precision']);
                $subject->setOutputMode($options['output_mode']);
                $variant = $subject->createVariant(new ParameterSet(0, $options['parameters']), $options['revs'], $options['warmup']);

                $time = $baseTime;

                foreach ($options['iterations'] as $time) {
                    $variant->createIteration(self::createResults($baseTime + $time, 200, 0));
                }

                $variant->computeStats();
                $baseTime++;
            }
        }

        $informations = [];

        foreach ($options['env'] as $name => $information) {
            $informations[] = new Information($name, $information);
        }
        $suite->setEnvInformations($informations);

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
