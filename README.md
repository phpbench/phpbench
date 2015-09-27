![PHPBench Logo](https://avatars3.githubusercontent.com/u/12785153?v=3&s=100)

PhpBench
========

[![Build Status](https://travis-ci.org/phpbench/phpbench.svg?branch=master)](https://travis-ci.org/phpbench/phpbench)
[![StyleCI](https://styleci.io/repos/34982189/shield)](https://styleci.io/repos/34982189)

PhpBench is a benchmarking framework for PHP.

Features:

- Generate reports and render them to different mediums (Console, HTML,
  Markdown, etc).
- Benchmarks are executed in a separate process, effectively with no overhead.
- Memory usage statistics in addition to time.
- Support for parameterized benchmarking cases.
- Per-project configuration.
- Dump benchmark results as XML and generate reports later
- Run one or more (setup) parameterized methods before the benchmark
- Nice command line interface
- Implement your own Report Generators
- Implement your own Progress Loggers
- Utilizes [Tabular](https://github.com/phpbench/Tabular) for creating custom
  reports.
- More

````bash
$ phpbench run examples/CostOfReflectionBench.php --report=aggregate
PhpBench 0.5. Running benchmarks.

......
Done (6 subjects, 24 iterations) in 0.94s

+-----------------------+----------------------------+-------+--------+-------+-------+----------+--------+-----------+-----------+
| benchmark             | subject                    | group | params | revs  | iters | time     | memory | deviation | stability |
+-----------------------+----------------------------+-------+--------+-------+-------+----------+--------+-----------+-----------+
| CostOfReflectionBench | benchMethodSet             |       | []     | 40000 | 4     | 1.7951μs | 672b   | +80.57%   | 93.45%    |
| CostOfReflectionBench | benchPublicProperty        |       | []     | 40000 | 4     | 0.9941μs | 640b   | 0.00%     | 98.03%    |
| CostOfReflectionBench | benchPublicReflection      |       | []     | 40000 | 4     | 1.8264μs | 656b   | +83.72%   | 96.51%    |
| CostOfReflectionBench | benchPrivateReflection     |       | []     | 40000 | 4     | 1.8983μs | 656b   | +90.95%   | 95.63%    |
| CostOfReflectionBench | benchNewClass              |       | []     | 40000 | 4     | 1.1369μs | 576b   | +14.36%   | 97.88%    |
| CostOfReflectionBench | benchReflectionNewInstance |       | []     | 40000 | 4     | 1.5314μs | 592b   | +54.04%   | 96.96%    |
+-----------------------+----------------------------+-------+--------+-------+-------+----------+--------+-----------+-----------+
````

Documentation and Installation Instructions
-------------------------------------------

See the [official documentation](http://phpbench.readthedocs.org)

See also
--------

This library was influenced by the
[athletic](https://github.com/polyfractal/athletic) benchmarking framework.
