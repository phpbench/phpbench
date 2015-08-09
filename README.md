PhpBench
========

[![Build Status](https://travis-ci.org/phpbench/phpbench.svg?branch=master)](https://travis-ci.org/phpbench/phpbench)

PhpBench is a benchmarking framework for PHP.

Features:

- Generate reports
- Records memory statistics
- Run iterations in separate processes
- Support for parameterized benchmarking cases and matrixes
- Per-project configuration
- Dump benchmark results as XML and generate reports later
- Run one or more (setup) parameterized methods before the benchmark
- Nice command line interface
- Add custom report generators
- Add custom progress loggers
- More

**DISCLAIMER**: This library is in rapid develpomenet and it should not be
considered stable. The official documentation is a work in progress and this
documentation could be incorrect.

Why?
----

There already exists other benchmarking frameworks, why another?

All of the existing frameworks (as far as I can see) are designed for
benchmarking algorithms or other relatively quick scenarios. They are the
equivalent of "unit" tests.

PhpBench is designed also for running larger benchmark suites which may take serveral
minutes to complete, it could be seen as a *system* benchmarking framework,
and therefore analagous to integration testing. To this end it allows
benchmarks to be iterated in separate processes and 

Installation
------------

Install with composer, add it to `composer.json`:

````javascript
{
    "phpbench/phpbench": "~1.0@dev"
}
````

How it works
------------

PhpBench is inspired by [PHPUnit](https://github.com/phpunit/phpunit).
Basically, you create a class. Each method which begins with `bench` is
executed by the benchmark runner and the time it took to execute it is
recorded.

The class name must end with `Bench` (this is just to optimize finding
benchmark files).

The method is known as the benchmark *subject* and each method optionally
accepts an `Iteration` from which can be accessed contextual information.

You can annotate **both** the class and the subject methods with any of the 
following annotations:

### @group

**plural**

Assign the annotated method or class instance in the named group.

### @revs

**plural**

Number of times the subject should be *consecutively* executed within a single
iteration. Use this for measuring the speed of things at the *microsecond*
level. You can declare this multiple times.

### @iterations

**singular**

Define the number of iterations that should be *measured*. The difference
between this and `@revs` is that revs happen in a single measurement, whereas
each iteration is recorded separately. 

This can be useful for seeing how well larger operations scale on consecutive
calls, it is not a good way to measure things that happen at the microsecond
level however, where you will want to perform tens of thousands of repetitions
as the time is measured per repetition so the results will be at a lower
resolution.

### @beforeMethod

**plural**

Specify a method which should be executed before the subject.

Multiple before methods can be specified.

### @afterMethod

**plural**

Specify a method which should be executed after the subject.

Multiple after methods can be specified.

### @paramProvider

**plural**

Specify a method which will provide parameters. Parameters are passed as an
argument to the benchmarking subject method.

If multiple parameter providers are specified, then the they will be combined
in to a cartesian product.

Reports
-------

Reports can be specified simply as:

````bash
$ php vendor/bin/phpbench run benchmarks/ --report=<report name>
````

But you can also pass configuration to the report:

````bash
$ php vendor/bin/phpbench run benchmarks/ --report='{"generator":
"console_table", "sort": ["time"]}'
````

Above we use the `console_table` generator, which is the generator used to
created tabular reports in the CLI. The rest of the options are passed to the
generator.

The default reports are:

- **full**: Console report using the default options of the `console_table`
  generator.
- **simple**: Minimal console report
- **aggregate**: Console report which aggregates iterations and shows averages
  and other metrics such as stability.

You can extend existing configurations:

````bash
$ php vendor/bin/phpbench run benchmarks/ --report='{"extends":
"aggregate", "sort": ["time"]}'
````

Please see the `PhpBench\Report\Generator\ConsoleTableGenerator` class to
learn the full set of options.

Report Generators
-----------------

Report generators are the classes which generate reports.

- `composite`: Report which accepts an array of reports to generate. Use this
  to generate multiple reports at one time.
- `console_table`: Generate tabular reports in the CLI.

Dumping XML and deferring reports
---------------------------------

Benchmark suites could take an unreasonable amount of time to compete, reports
on the other hand are created in milliseconds. Therefore debugging or tuning
reports could be very tedious indeed.

PhpBench allows you to dump an XML representation of the results from which
reports can be generated:

````bash
$ php vendor/bin/phpbench run path/to/benchmarks --dump-file=results.xml
````

And then you can generate the reports using:

````bash
$ php vendor/bin/phpbench report resuts.xml --report=console_table
````

This is also a great way to compare benchmarks, for example accross git
branches.

Example
-------

````php
<?php
use PhpBench\Benchmark\Iteration;
use PhpBench\BenchmarkInterface;

/**
 * Annotations in the class doc are applied to each method
 *
 * @revs 1000
 */
class SomeBenchmarkBench implements BenchmarkInterface
{
    public function benchRandom(Iteration $iteration)
    {
        usleep(rand(0, 50000));
    }

    /**
     * @iterations 3
     */
    public function benchDoNothing(Iteration $iteration)
    {
    }

    public function benchSomethingIsolated()

    /**
     * @paramProvider provideParamsOne
     * @paramProvider provideParamsTwo
     * @iterations 1
     */
    public function benchParameterized(Iteration $iteration)
    {
        // do something with a parameter
        $param = $iteration->getParameter('length');
    }

    public function provideParamsOne()
    {
        return array(
            array('length' => '1'),
            array('length' => '2'),
        );
    }

    public function provideParamsTwo()
    {
        return array(
            array('strategy' => 'left'),
            array('strategy' => 'right'),
        );
    }
}
````

You can then run the benchmark:

````bash
./bin/phpbench run tests/Functional/benchmarks/BenchmarkBench.php --report=simple --group=parameterized
PhpBench 0.5. Running benchmarks.

....
Done (4 subjects, 4 iterations) in 0.00s

+--------------------+-----------+------------+----------+------------+-----------+
| Subject            | Sum Revs. | Nb. Iters. | Av. Time | Av. RPS    | Deviation |
+--------------------+-----------+------------+----------+------------+-----------+
| benchParameterized | 1         | 0          | 7μs      | 142,857rps | +40.00%   |
| benchParameterized | 1         | 0          | 5μs      | 200,000rps | 0.00%     |
| benchParameterized | 1         | 0          | 5μs      | 200,000rps | 0.00%     |
| benchParameterized | 1         | 0          | 5μs      | 200,000rps | 0.00%     |
+--------------------+-----------+------------+----------+------------+-----------+

````

Above we use the `simple` report and specify to only run the reports from the
group `parameterized`.

Configuration
-------------

Document the configuration here.

Extending
---------

Document the extension API here.

See also
--------

This library was influenced by the
[athletic](https://github.com/polyfractal/athletic) benchmarking framework.
