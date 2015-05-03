PhpBench
========

PhpBench is a benchmarking framework for PHP.

Features:

- Support for parameterized benchmarking cases and matrixes
- Run one or more methods before the bench mark (also parameterized)
- Generate reports
- Nice command line interface

Note this library is currently under development.

Usage
-----

````bash
./bin/phpbench phpbench:run tests/assets/functional/BenchmarkCase.php
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

See also
--------

This library was influenced by the
[athletic](https://github.com/polyfractal/athletic) benchmarking framework.
