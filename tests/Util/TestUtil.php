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

use Prophecy\Prophecy\ObjectProphecy;

/**
 * Utility class for configuring benchmarking prophecy objects.
 */
class TestUtil
{
    public static function configureSubject(ObjectProphecy $subject, array $options = array())
    {
        $options = array_merge(array(
            'iterations' => 1,
            'name' => 'benchFoo',
            'beforeMethods' => array(),
            'afterMethods' => array(),
            'parameterSets' => array(array(array())),
            'groups' => array(),
            'revs' => 1,
            'notApplicable' => false,
            'skip' => false,
            'sleep' => 0,
            'paramProviders' => array(),
            'outputTimeUnit' => 'microseconds',
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
        $subject->getParamProviders()->willReturn($options['paramProviders']);
        $subject->getOutputTimeUnit()->willReturn($options['outputTimeUnit']);
    }

    public static function configureBenchmark(ObjectProphecy $benchmark, array $options = array())
    {
        $options = array_merge(array(
            'class' => 'Benchmark',
            'beforeClassMethods' => array(),
            'afterClassMethods' => array(),
            'beforeMethods' => array(),
            'afterMethods' => array(),
            'path' => null,
        ), $options);

        $benchmark->getClass()->willReturn($options['class']);
        $benchmark->getBeforeClassMethods()->willReturn($options['beforeClassMethods']);
        $benchmark->getAfterClassMethods()->willReturn($options['afterClassMethods']);
        $benchmark->getBeforeMethods()->willReturn($options['beforeMethods']);
        $benchmark->getAfterMethods()->willReturn($options['afterMethods']);
        $benchmark->getPath()->willReturn($options['path']);
    }
}
