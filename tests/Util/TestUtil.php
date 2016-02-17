<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Util;

use PhpBench\Environment\Information;
use PhpBench\Model\ParameterSet;
use PhpBench\Model\Suite;
use PhpBench\Model\SuiteCollection;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * Utility class for configuring benchmarking prophecy objects.
 */
class TestUtil
{
    public static function configureSubjectMetadata(ObjectProphecy $subject, array $options = array())
    {
        $options = array_merge(array(
            'iterations' => 1,
            'name' => 'benchFoo',
            'beforeMethods' => array(),
            'afterMethods' => array(),
            'parameterSets' => array(array(array())),
            'groups' => array(),
            'revs' => 1,
            'warmup' => 0,
            'notApplicable' => false,
            'skip' => false,
            'sleep' => 0,
            'paramProviders' => array(),
            'outputTimeUnit' => 'microseconds',
            'outputMode' => 'time',
        ), $options);

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

    public static function configureBenchmarkMetadata(ObjectProphecy $benchmark, array $options = array())
    {
        $options = array_merge(array(
            'class' => 'Benchmark',
            'beforeClassMethods' => array(),
            'afterClassMethods' => array(),
            'path' => null,
        ), $options);

        $benchmark->getClass()->willReturn($options['class']);
        $benchmark->getBeforeClassMethods()->willReturn($options['beforeClassMethods']);
        $benchmark->getAfterClassMethods()->willReturn($options['afterClassMethods']);
        $benchmark->getPath()->willReturn($options['path']);
    }

    public static function createSuite(array $options = array())
    {
        $options = array_merge(array(
            'date' => '2016-02-06',
            'revs' => 5,
            'warmup' => 10,
            'sleep' => 1,
            'basetime' => 10,
            'groups' => array(),
            'name' => 'test',
            'benchmarks' => array('TestBench'),
            'parameters' => array(),
            'groups' => array('one', 'two', 'three'),
            'parameters' => array(
                'param1' => 'value1',
            ),
            'subjects' => array('benchOne'),
            'env' => array(),
            'output_time_unit' => 'microseconds',
            'output_time_precision' => 7,
            'output_mode' => 'time',
        ), $options);

        $dateTime = new \DateTime($options['date']);
        $suite = new Suite(
            $options['name'],
            $dateTime,
            null
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
                $variant->createIteration($baseTime, 200, 0);
                $variant->createIteration($baseTime + 10, 200, 0);
                $variant->computeStats();
                $baseTime++;
            }
        }

        $informations = array();
        foreach ($options['env'] as $name => $information) {
            $informations[] = new Information($name, $information);
        }
        $suite->setEnvInformations($informations);

        return $suite;
    }

    public static function createCollection(array $suiteConfigs = array())
    {
        $suites = array();
        foreach ($suiteConfigs as $suiteConfig) {
            $suites[] = self::createSuite($suiteConfig);
        }

        return new SuiteCollection($suites);
    }
}
