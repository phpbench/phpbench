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

use InvalidArgumentException;
use PhpBench\Benchmark\RunnerConfig;
use PhpBench\Tests\TestCase;

class RunnerConfigTest extends TestCase
{
    private const TEST_TAG_NAME = 'tag_name';
    private const TEST_FILTERS = ['filter_one', 'filter_two'];
    private const TEST_ITERATIONS = [5];
    private const TEST_REVOLUTIONS = [6];
    private const TEST_PARAMETERS = ['one' => 1];
    private const TEST_SLEEP = 100;
    private const TEST_RETRY_THRESHOLD = 10;
    private const TEST_WARMUP = [10];
    private const TEST_GROUPS = ['group1'];
    private const TEST_OUTPUT_TIME_UNIT = 'milliseconds';
    private const TEST_OUTPUT_TIME_PRECISION = 2;
    private const TEST_EXECUTOR = 'microtimre';
    private const TEST_STOP_ON_ERROR = true;
    private const TEST_ASSERTIONS = ['x > y'];

    /**
     * It should throw an exception if the retry threshold is less than zero.
     *
     */
    public function testRetryLessThanZetro(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must be greater');
        RunnerConfig::create()
            ->withRetryThreshold(-1);
    }

    /**
     * It should throw an exception if the revolutions are less than zero.
     *
     */
    public function testRevolutionsLessThanZero(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('greater than');
        RunnerConfig::create()->withRevolutions([-1]);
    }

    public function testBuild(): void
    {
        $config = RunnerConfig::create()
            ->withTag(self::TEST_TAG_NAME)
            ->withFilters(self::TEST_FILTERS)
            ->withIterations(self::TEST_ITERATIONS)
            ->withRevolutions(self::TEST_REVOLUTIONS)
            ->withParameters(self::TEST_PARAMETERS)
            ->withSleep(self::TEST_SLEEP)
            ->withWarmup(self::TEST_WARMUP)
            ->withGroups(self::TEST_GROUPS)
            ->withOutputTimeUnit(self::TEST_OUTPUT_TIME_UNIT)
            ->withOutputTimePrecision(self::TEST_OUTPUT_TIME_PRECISION)
            ->withExecutor(self::TEST_EXECUTOR)
            ->withStopOnError(self::TEST_STOP_ON_ERROR)
            ->withAssertions(self::TEST_ASSERTIONS)
            ->withRetryThreshold(self::TEST_RETRY_THRESHOLD);

        $this->assertEquals(self::TEST_TAG_NAME, $config->getTag());
        $this->assertEquals(self::TEST_ITERATIONS, $config->getIterations());
        $this->assertEquals(self::TEST_REVOLUTIONS, $config->getRevolutions());
        $this->assertEquals([[self::TEST_PARAMETERS]], $config->getParameterSets());
        $this->assertEquals(self::TEST_SLEEP, $config->getSleep());
        $this->assertEquals(self::TEST_RETRY_THRESHOLD, $config->getRetryThreshold());
    }

    /**
     * Default should be used if no explict value set in the context.
     */
    public function testDefaults(): void
    {
        $config = RunnerConfig::create();
        $this->assertEquals([self::TEST_RETRY_THRESHOLD], $config->getIterations([self::TEST_RETRY_THRESHOLD]));
        $this->assertEquals([self::TEST_RETRY_THRESHOLD], $config->getRevolutions([self::TEST_RETRY_THRESHOLD]));
        $this->assertEquals(self::TEST_RETRY_THRESHOLD, $config->getRetryThreshold(self::TEST_RETRY_THRESHOLD));
    }

    /**
     * Defaults should be ignored if explicit values are set in the context.
     */
    public function testOverride(): void
    {
        $config = RunnerConfig::create()
            ->withIterations(self::TEST_ITERATIONS)
            ->withRevolutions(self::TEST_REVOLUTIONS)
            ->withRetryThreshold(self::TEST_RETRY_THRESHOLD);

        $this->assertEquals(self::TEST_ITERATIONS, $config->getIterations([20]));
        $this->assertEquals(self::TEST_REVOLUTIONS, $config->getRevolutions([30]));
        $this->assertEquals(self::TEST_RETRY_THRESHOLD, $config->getRetryThreshold(40));
    }

    /**
     * The overridden parameter sets should be nested in an array of an array.
     */
    public function testGetParameterSets(): void
    {
        $config = RunnerConfig::create()
            ->withParameters([
                'nb_elements' => 10,
            ]);

        $this->assertEquals(
            [
                [
                    [
                        'nb_elements' => 10,
                    ],
                ],
            ],
            $config->getParameterSets(
                [
                    [
                        'nb_elements' => 5,
                    ],
                ]
            )
        );
    }
}
