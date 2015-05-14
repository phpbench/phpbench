PhpBench
========

PhpBench is a benchmarking framework for PHP.

Features:

- Support for parameterized benchmarking cases and matrixes
- Run one or more (setup) parameterized methods before the benchmark
- Generate reports
- Dump benchmark results as XML and generate reports later
- Nice command line interface

Note this library is currently under development.

Why?
----

There already exists other benchmarking frameworks, why another?

All of the existing frameworks (as far as I can see) are designed for
benchmarking algorithms or other relatively quick scenarios. They are the
equivalent of "unit" tests.

PhpBench is designed for running BIG benchmark suites which may take serveral
minutes to complete, it could be seen as a *system* benchmarking framework,
and therefore analagous to integration testing.

Saying that, it should work equally well for testing smaller "units".

How it works
------------

PhpBench is inspired by PhpUnit. Basically, you create a class. Each method
which begins with `bench` is executed by the benchmark runner and the time it
took to execute it is recorded.

The method is known as the benchmark *subject* and each method accepts an 
`Iteration` from which can be accessed contextual information.

You must annotate the method as follows:

### @description

A description of the benchmark subject

### @iterations

Define how many timesw the method should be executed.

### @beforeMethod

Specify a method which should be executed before the subject. The before
method also accepts an `Iteration` object and has access to the iteration
context.

Zero or many before methods can be specified

### @paramProvider

Specify a method which will provide parameters which can be accessed from the
`Iteration` object by both the before method and the subject method.

If multiple parameter providers are specified, then the they will be combined
in to a cartesian product.

Reports
-------

Reports can be specified simply as:

````bash
$ <path to>/phpbench run benchmarks/ --report=<report name>
````

But you can also pass configuration to the report, in this case you must pass
a JSON string which should have a `name` key specifying the name of the
report, all other keys are interpreted as options:

````bash
$ <path to>/phpbench run benchmarks/ --report='{"name": "console_table",
"memory": true}'
````

Dumping XML and deferring reports
---------------------------------

Benchmark suites could take an unreasonable amount of time to compete, reports
on the other hand are created in milliseconds. Therefore debugging or tuning
reports could be very tedious indeed.

PhpBench allows you to dump an XML representation of the results from which
reports can be generated:

````bash
$ <path to>/phpbench run path/to/benchmarks --dumpfile=results.xml
````

And then you can generate the reports using:

````bash
$ <path to>/phpbench report resuts.xml --report=console_table
````

Example
-------

````php
<?php

use PhpBench\BenchCase;
use PhpBench\BenchIteration;

class BenchmarkCase implements BenchCase
{
    /**
     * @description randomBench
     */
    public function benchRandom(BenchIteration $iteration)
    {
        usleep(rand(0, 50000));
    }

    /**
     * @iterations 3
     * @description Do nothing three times
     */
    public function benchDoNothing(BenchIteration $iteration)
    {
       // do something time consuming
    }

    /**
     * @paramProvider provideParamsOne
     * @paramProvider provideParamsTwo
     * @beforeMethod prepareParameterized
     * @description Parameterized bench mark
     * @iterations 3
     */
    public function benchParameterized(BenchIteration $iteration)
    {
         $param1 = $iteration->getParameter('length');

         // ...
    }

    public function prepareParameterized(BenchIteration $iteration)
    {
        if ($iteration->getIndex() === 0) {
            // do something on the first iteration
        }

        $param = $iteration->getParameter('nb_nodes');

        // ...
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
$ ./<path to>/phpbench phpbench:run tests/assets/functional/BenchmarkCase.php
Running benchmarking suite

BenchmarkCase
...

Generating reports..

BenchmarkCase#benchRandom(): randomBench
+---+--------+------------+
| # | Params | Time       |
+---+--------+------------+
| 1 | []     | 0.03885007 |
+---+--------+------------+

BenchmarkCase#benchDoNothing(): Do nothing three times
+---+--------+------------+
| # | Params | Time       |
+---+--------+------------+
| 1 | []     | 0.00001001 |
| 2 | []     | 0.00000691 |
| 3 | []     | 0.00000715 |
+---+--------+------------+

BenchmarkCase#benchParameterized(): Parameterized bench mark
+---+-----------------------------------+------------+
| # | Params                            | Time       |
+---+-----------------------------------+------------+
| 1 | {"length":"1","strategy":"left"}  | 0.00000882 |
| 1 | {"length":"2","strategy":"left"}  | 0.00000715 |
| 1 | {"length":"1","strategy":"right"} | 0.00000811 |
| 1 | {"length":"2","strategy":"right"} | 0.00000787 |
+---+-----------------------------------+------------+
````

See also
--------

This library was influenced by the
[athletic](https://github.com/polyfractal/athletic) benchmarking framework.
