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

namespace PhpBench\Tests\System;

class RunTest extends SystemTestCase
{
    /**
     * It should use a speified, valid, configuration.
     */
    public function testSpecifiedConfig()
    {
        $process = $this->phpbench('run --verbose --config=env/config_valid/phpbench.json');
        $this->assertExitCode(0, $process);
        $this->assertStringContainsString('best [mean mode] worst', $process->getOutput());
    }

    /**
     * It should use phpbench.json if present
     * It should prioritize phpbench.json over .phpbench.dist.json.
     */
    public function testPhpBenchConfig()
    {
        $process = $this->phpbench('run', 'env/config_valid');
        $this->assertExitCode(0, $process);
        $this->assertStringContainsString('best [mean mode] worst', $process->getOutput());
    }

    /**
     * It should use phpbench.json.dist if present.
     */
    public function testPhpBenchDistConfig()
    {
        $process = $this->phpbench('run', 'env/config_dist');
        $this->assertExitCode(0, $process);
        $this->assertStringContainsString('best [mean mode] worst', $process->getOutput());
    }

    /**
     * It should run when given a path.
     * It should show the default (simple) report.
     */
    public function testCommand()
    {
        $process = $this->phpbench('run benchmarks/set4/NothingBench.php');
        $this->assertExitCode(0, $process);
    }

    /**
     * It should run and generate a named report.
     */
    public function testCommandWithReport()
    {
        $process = $this->phpbench('run benchmarks/set4/NothingBench.php --report=default');
        $this->assertExitCode(0, $process);
        $output = $process->getOutput();
        $this->assertStringContainsString('bench', $output);
    }

    /**
     * It should show an error if no path is given (and no path is configured).
     */
    public function testCommandWithNoPath()
    {
        $process = $this->phpbench('run');
        $this->assertExitCode(1, $process);
        $this->assertStringContainsString('You must either specify', $process->getErrorOutput());
    }

    /**
     * It should run and generate a report configuration.
     */
    public function testCommandWithReportConfiguration()
    {
        $process = $this->phpbench(
            'run benchmarks/set4/NothingBench.php --report=\'{"extends": "default"}\''
        );
        $this->assertExitCode(0, $process);
        $output = $process->getOutput();
        $this->assertStringContainsString('benchNothing', $output);
    }

    /**
     * It should fail if an unknown report name is given.
     */
    public function testCommandWithReportConfigurationUnknown()
    {
        $process = $this->phpbench(
            'run --report=\'{"generator": "foo_table"}\' benchmarks/set4/NothingBench.php'
        );
        $this->assertExitCode(1, $process);
        $this->assertStringContainsString('generator service "foo_table" does not exist', $process->getErrorOutput());
    }

    /**
     * It should fail if an invalid report configuration is given.
     */
    public function testCommandWithReportConfigurationInvalid()
    {
        $process = $this->phpbench(
            'run --report=\'{"name": "foo_ta\' benchmarks/set4/NothingBench.php'
        );
        $this->assertExitCode(1, $process);
        $this->assertStringContainsString('Parse error', $process->getErrorOutput());
    }

    /**
     * It should fail if an invalid report name is provided.
     */
    public function testFailInvalidReportName()
    {
        $process = $this->phpbench(
            'run --report=foobar benchmarks/set4/NothingBench.php'
        );
        $this->assertExitCode(1, $process);
        $this->assertStringContainsString('No generator configuration named "foobar" exists.', $process->getErrorOutput());
    }

    /**
     * It should fail if there is an assertion failure.
     */
    public function testFailAssertionFailure()
    {
        $process = $this->phpbench(
            'run benchmarks/set5/AssertFailBench.php'
        );
        $this->assertExitCode(2, $process);
    }

    /**
     * It should not fail if there are warnings.
     */
    public function testFailAssertionWarning()
    {
        $process = $this->phpbench(
            'run benchmarks/set5/AssertWarnBench.php'
        );
        $this->assertExitCode(0, $process);
    }

    /**
     * If passed the tolerate-failure option, it should return 0 exit code even when failures are encountered.
     */
    public function testFailAssertionFailureTolerate()
    {
        $process = $this->phpbench(
            'run benchmarks/set5/AssertFailBench.php --tolerate-failure'
        );
        $this->assertExitCode(0, $process);
    }

    /**
     * It should override assertions.
     */
    public function testFailAssertionOverride()
    {
        $process = $this->phpbench(
            'run benchmarks/set5/AssertFailBench.php --assert="{stat: mean, value: 1000}"'
        );
        $this->assertExitCode(0, $process);
    }

    /**
     * It should dump none to an XML file.
     */
    public function testDumpXml()
    {
        $process = $this->phpbench(
            'run --dump-file=' . $this->fname . ' benchmarks/set4/NothingBench.php'
        );
        $this->assertExitCode(0, $process);
        $output = $process->getOutput();
        $this->assertStringContainsString('Dumped', $output);
        $this->assertFileExists($this->fname);
    }

    /**
     * It should dump to stdout.
     */
    public function testDumpXmlStdOut()
    {
        $process = $this->phpbench(
            'run --dump --progress=none benchmarks/set1/BenchmarkBench.php'
        );
        $this->assertExitCode(0, $process);
        $output = $process->getOutput();
        $this->assertXPathCount(3, $output, '//subject');
    }

    /**
     * It should accept explicit parameters.
     */
    public function testOverrideParameters()
    {
        $process = $this->phpbench(
            'run --dump --progress=none --parameters=\'{"length": 333}\' benchmarks/set1/BenchmarkBench.php'
        );
        $this->assertExitCode(0, $process);
        $output = $process->getOutput();
        $this->assertXPathCount(3, $output, '//parameter[@value=333]');
    }

    /**
     * It should throw an exception if an invalid JSON string is provided for parameters.
     */
    public function testOverrideParametersInvalidJson()
    {
        $process = $this->phpbench(
            'run --dump --progress=none --parameters=\'{"length": 333\' benchmarks/set1/BenchmarkBench.php'
        );

        $this->assertExitCode(1, $process);
        $this->assertStringContainsString('Could not decode', $process->getErrorOutput());
    }

    /**
     * Its should allow the number of iterations to be specified.
     */
    public function testOverrideIterations()
    {
        $process = $this->phpbench(
            'run --filter=benchDoNothing --progress=none --dump --iterations=10 benchmarks/set1/BenchmarkBench.php'
        );

        $this->assertExitCode(0, $process);
        $output = $process->getOutput();
        $this->assertXPathCount(10, $output, '//subject[@name="benchDoNothing"]//iteration');
    }

    /**
     * It should override revolutions.
     */
    public function testOverrideRevolutions()
    {
        $process = $this->phpbench(
            'run --filter=benchDoNothing --progress=none --dump --revs=666 benchmarks/set1/BenchmarkBench.php'
        );

        $this->assertExitCode(0, $process);
        $output = $process->getOutput();
        $this->assertXPathExpression(666, $output, 'number(//subject[@name="benchDoNothing"]//variant/@revs)');
    }

    /**
     * Its should allow the time unit to be specified.
     */
    public function testOverrideTimeUnit()
    {
        $process = $this->phpbench(
            'run --filter=benchDoNothing --time-unit=milliseconds --iterations=10 benchmarks/set1/BenchmarkBench.php'
        );

        $this->assertExitCode(0, $process);
        $this->assertStringContainsString('(ms)', $process->getOutput());
    }

    /**
     * Its should allow the mode to be specified.
     */
    public function testOverrideMode()
    {
        $process = $this->phpbench(
            'run --filter=benchDoNothing --mode=throughput --iterations=10 benchmarks/set1/BenchmarkBench.php'
        );

        $this->assertExitCode(0, $process);
        $this->assertStringContainsString('(ops/μs)', $process->getOutput());
    }

    /**
     * It should set the bootstrap file.
     */
    public function testSetBootstrap()
    {
        // The foobar_bootstrap defines a single class which is used by FoobarBench
        $process = $this->phpbench(
            'run --bootstrap=bootstrap/foobar.bootstrap benchmarks/set2/FoobarBench.php'
        );

        $this->assertExitCode(0, $process);
    }

    /**
     * It should set the bootstrap file which contains variables that conflict with the
     * script templates.
     */
    public function testSetConflictBootstrap()
    {
        // The foobar_bootstrap defines a single class which is used by FoobarBench
        $process = $this->phpbench(
            'run --bootstrap=bootstrap/conflicting.bootstrap benchmarks/set2/FoobarBench.php'
        );

        $this->assertExitCode(0, $process);
    }

    /**
     * It should set the bootstrap using the short option.
     */
    public function testSetBootstrapShort()
    {
        // The foobar_bootstrap defines a single class which is used by FoobarBench
        $process = $this->phpbench(
            'run -b=bootstrap/foobar.bootstrap benchmarks/set2/FoobarBench.php'
        );

        $this->assertExitCode(0, $process);
    }

    /**
     * It should override the bootstrap file.
     */
    public function testOverrideBootstrap()
    {
        // The foobar_bootstrap defines a single class which is used by FoobarBench
        $process = $this->phpbench(
            'run --bootstrap=bootstrap/foobar.bootstrap benchmarks/set2/FoobarBench.php --config=env/config_valid/phpbench.json'
        );

        $this->assertExitCode(0, $process);
        $output = $process->getOutput();
        $this->assertStringContainsString('best [mean mode] worst', $output);
    }

    /**
     * It should load the configured bootstrap relative to the config file.
     */
    public function testConfigBootstrapRelativity()
    {
        // The foobar.bootstrap defines a single class which is used by FoobarBench
        $process = $this->phpbench(
            'run benchmarks/set2/FoobarBench.php --config=env/config_set2/phpbench.json'
        );

        $this->assertExitCode(0, $process);
    }

    /**
     * It can have the progress logger specified.
     * TODO: Make this a separate test and assert the output.
     *
     * @dataProvider provideProgressLoggers
     */
    public function testProgressLogger($progress)
    {
        $process = $this->phpbench(
            'run --progress=' . $progress . ' benchmarks/set1/BenchmarkBench.php'
        );
        $this->assertExitCode(0, $process);
    }

    public function provideProgressLoggers()
    {
        return [
            ['classdots'],
            ['dots'],
            ['verbose'],
            ['histogram'],
            ['blinken'],
            ['travis'],
            ['none'],
        ];
    }

    /**
     * It should run specified groups.
     */
    public function testGroups()
    {
        $process = $this->phpbench(
            'run --group=do_nothing --dump --progress=none benchmarks/set1/BenchmarkBench.php'
        );

        $this->assertExitCode(0, $process);
        $output = $process->getOutput();
        $this->assertXPathCount(1, $output, '//subject');
    }

    /**
     * It should generate in different output formats.
     *
     * @dataProvider provideOutputs
     */
    public function testOutputs($output)
    {
        $process = $this->phpbench(
            'run --output=' . $output . ' --report=default benchmarks/set1/BenchmarkBench.php'
        );

        $this->assertExitCode(0, $process);
    }

    public function provideOutputs()
    {
        return [
            ['html'],
            ['markdown'],
        ];
    }

    /**
     * It should set the retry threshold.
     */
    public function testRetryThreshold()
    {
        // use the debug executor, providing 3 groups of 4 iterations. the
        // first two are out of bounds the third is constant.
        $process = $this->phpbench(
            'run benchmarks/set4/NothingBench.php ' .
            '--executor=\'{"executor": "debug", "times": [10, 100, 10, 100, 50, 500, 50, 500, 1, 1, 1, 1]}\' '.
            '--retry-threshold=1 --iterations=2 '.
            '--dump'
        );
        $this->assertExitCode(0, $process);
        $output = $process->getOutput();
        $this->assertStringContainsString(' reject-count="4"', $output);
    }

    /**
     * It should set the sleep option.
     */
    public function testSleep()
    {
        $process = $this->phpbench(
            'run benchmarks/set4/NothingBench.php --sleep=5000'
        );

        $this->assertExitCode(0, $process);
    }

    /**
     * It should exit with code "1" if an exception is encountered while running
     * the iterations.
     */
    public function testIterationErrorExitCode()
    {
        $process = $this->phpbench(
            'run benchmarks/set3/ErrorBench.php'
        );

        $this->assertExitCode(1, $process);
        $this->assertStringContainsString('1 subjects encountered errors', $process->getOutput());
        $this->assertStringContainsString('benchNothingElse', $process->getOutput());
    }

    /**
     * It should stop on the first exception if an exception is encountered.
     */
    public function testIterationStopOnError()
    {
        $process = $this->phpbench(
            'run benchmarks/set3/ErrorBench.php --stop-on-error'
        );

        $this->assertExitCode(1, $process);
        $this->assertStringContainsString('1 subjects encountered errors', $process->getOutput());
        $this->assertStringNotContainsString('benchNothingElse', $process->getOutput());
    }

    /**
     * It should stop on the first exception if an exception is encountered
     * with many variants
     */
    public function testIterationStopOnErrorWithMultipleVariants()
    {
        $process = $this->phpbench(
            'run benchmarks/set3/ErrorVariantsBench.php --stop-on-error'
        );

        $this->assertExitCode(1, $process);
        $this->assertStringContainsString('1 subjects encountered errors', $process->getOutput());
        $this->assertStringNotContainsString('benchNothingElse', $process->getOutput());
    }
    /**
     * It should allow the precision to be set.
     */
    public function testPrecision()
    {
        $process = $this->phpbench(
            'run benchmarks/set4/NothingBench.php --precision=6'
        );

        $this->assertExitCode(0, $process);
        $success = preg_match('{[0-9]\.([0-9]+)μs}', $process->getOutput(), $matches);
        $this->assertEquals(1, $success);
        $this->assertEquals(6, strlen($matches[1]));
    }

    /**
     * It should request that the suite result to be stored.
     */
    public function testStore()
    {
        $process = $this->phpbench(
            'run benchmarks/set4/NothingBench.php --store'
        );

        $this->assertExitCode(0, $process);
    }

    public function testTaggingImplicitlyStoresResult()
    {
        $process = $this->phpbench(
            'run benchmarks/set4/NothingBench.php --tag=foobar'
        );

        $this->assertExitCode(0, $process);

        $process = $this->phpbench(
            'report --report=aggregate --uuid=tag:foobar'
        );

        $this->assertExitCode(0, $process);
    }

    /**
     * It should run with the memory-centric-microtime executor
     */
    public function testRunsWithMemoryExecutor()
    {
        $process = $this->phpbench(
            'run benchmarks/set4/NothingBench.php --executor=memory_centric_microtime --iterations=1 --revs=1'
        );

        // because there is no storage driver by default the factory will throw an exception.
        $this->assertExitCode(0, $process);
    }

    /**
     * It should not crash when zeros are reported as times.
     */
    public function testZeroTimedIterations()
    {
        $process = $this->phpbench(
            'run benchmarks/set1 --executor=\'{"executor": "debug", "times": [0]}\''
        );
        $this->assertExitCode(0, $process);
    }

    /**
     * It should set the PHP binary, wrapper and config.
     */
    public function testPhpEnvOptions()
    {
        $process = $this->phpbench(
            'run benchmarks/set4/NothingBench.php --php-binary=php --php-config="memory_limit: 10M" --php-wrapper="env"'
        );
        $this->assertExitCode(0, $process);
    }

    /**
     * It should disable the PHP ini file.
     */
    public function testPhpDisableIni()
    {
        $process = $this->phpbench(
            'run benchmarks/set4/NothingBench.php --php-disable-ini --php-config="extension:json.so" --report=env'
        );
        $this->assertExitCode(0, $process);
        $this->assertRegExp('{ini\s+\| no}', $process->getOutput());
    }

    public function testErrorWhenTimeoutExceeded()
    {
        $process = $this->phpbench(
            'run benchmarks/set6/TimeoutBench.php'
        );
        $this->assertExitCode(1, $process);
    }
}
