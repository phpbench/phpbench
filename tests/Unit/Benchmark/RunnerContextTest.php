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

namespace PhpBench\Tests\Unit\Benchmark;

use PhpBench\Benchmark\RunnerContext;

class RunnerContextTest extends \PHPUnit_Framework_TestCase
{
    /**
     * It should throw an exception if the path is null.
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage You must either specify or configure a path where your benchmarks can be found.
     */
    public function testNullPath()
    {
        new RunnerContext(null);
    }

    /**
     * It should throw an exception if the retry threshold is not numeric.
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Retry threshold must be a number
     */
    public function testRetryNotNumeric()
    {
        new RunnerContext(__DIR__, [
            'retry_threshold' => 'asd',
        ]);
    }

    /**
     * It should throw an exception if the retry threshold is less than zero.
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage must be greater
     */
    public function testRetryLessThanZetro()
    {
        new RunnerContext(__DIR__, [
            'retry_threshold' => -1,
        ]);
    }

    /**
     * It should throw an exception if the iterations is not numeric.
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Iterations must be a number
     */
    public function testIterationsNotNumeric()
    {
        new RunnerContext(__DIR__, [
            'iterations' => ['asd'],
        ]);
    }

    /**
     * It should throw an exception if the revolutions are not numeric.
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Revolutions must be a number
     */
    public function testRevolutionsNotNumeric()
    {
        new RunnerContext(__DIR__, [
            'revolutions' => ['asd'],
        ]);
    }

    /**
     * It should throw an exception if the warmup is not numeric.
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Warmup must be a number
     */
    public function testWarmupNotNumeric()
    {
        new RunnerContext(__DIR__, [
            'warmup' => ['asd'],
        ]);
    }

    /**
     * It should throw an exception if unrecognized options are given.
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid options "abar", "baar"
     */
    public function testUnknownOptions()
    {
        new RunnerContext(__DIR__, [
            'abar' => 'invalid',
            'baar' => 'dilavni',
        ]);
    }

    /**
     * It should throw an exception if iterations is not numeric.
     */
    public function testGetters()
    {
        $options = [
            'context_name' => 'my_context',
            'filters' => ['filter_one', 'filter_two'],
            'iterations' => [5],
            'revolutions' => [6],
            'parameters' => ['one' => 2],
            'sleep' => 100,
            'retry_threshold' => 10,
        ];

        $context = new RunnerContext(
            '/path/to',
            $options
        );

        $this->assertEquals('/path/to', $context->getPath());
        $this->assertEquals($options['context_name'], $context->getContextName());
        $this->assertEquals($options['filters'], $context->getFilters());
        $this->assertEquals($options['iterations'], $context->getIterations());
        $this->assertEquals($options['revolutions'], $context->getRevolutions());
        $this->assertEquals([[$options['parameters']]], $context->getParameterSets());
        $this->assertEquals($options['sleep'], $context->getSleep());
        $this->assertEquals(10, $context->getRetryThreshold());
    }

    /**
     * Default should be used if no explict value set in the context.
     */
    public function testDefaults()
    {
        $context = new RunnerContext(__DIR__);
        $this->assertEquals([10], $context->getIterations([10]));
        $this->assertEquals([10], $context->getRevolutions([10]));
        $this->assertEquals(10, $context->getRetryThreshold(10));
    }

    /**
     * Defaults should be ignored if explicit values are set in the context.
     */
    public function testOverride()
    {
        $context = new RunnerContext(
            __DIR__,
            [
                'iterations' => [20],
                'revolutions' => [30],
                'retry_threshold' => 40,
            ]
        );
        $this->assertEquals([20], $context->getIterations([10]));
        $this->assertEquals([30], $context->getRevolutions([10]));
        $this->assertEquals(40, $context->getRetryThreshold(10));
    }

    /**
     * The overridden parameter sets should be nested in an array of an array.
     */
    public function testGetParameterSets()
    {
        $context = new RunnerContext(
            __DIR__,
            [
                'parameters' => [
                    'nb_elements' => 10,
                ],
            ]
        );

        $this->assertEquals(
            [
                [
                    [
                        'nb_elements' => 10,
                    ],
                ],
            ],
            $context->getParameterSets(
                [
                    [
                        'nb_elements' => 100,
                    ],
                ]
            )
        );
    }
}
