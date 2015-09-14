Introduction
============

PHPBench is a benchmark runner for PHP. It enables you to write standard
benchmarks for your application and helps you to make smart decisions based on
*comparative* benchmark results.

Features at a glance:

- **Process Isolation**: Benchmarks are run in separate processes with no
  significant overhead from the runner.
- **Reporting**: Powerful and extensible reports thanks to the `Tabular`_ library.
- **Revolutions and Iterations**: Spin and repeat.
- **Memory Usage**: Keep an eye on the amount of memory used by benchmarking
  subjects.
- **Deferred Reporting**: Dump benchmarking results to an XML file and report
  on them later.

Why PHPBench?
-------------

Performance can be monitored and measured in a number of ways: profiling (via.
`XDebug`_ or `Blackfire`_), injecting timing classes (e.g. `Symfony Stopwatch`_, `Hoa
Bench`_) or with server tools such as `NewRelic_`.

All of these methods can be useful, and some, essential, but they are not
ideally suited to comparative benchmarking or algorithm optimisation.

What PHPBench offers is the ability to benchmark *parts* of your
code and obtain a degree of *confidence* about the results by running multiple
iterations of the benchmark to determine if the result is stable.

As a tool it is highly analogous to the test framework `PHPUnit_`, but instead of *tests* we run
*benchmarks* and generate reports.

I Don't Like PHPBench
---------------------

Use `Athletic`_ .

.. _Athletic: https://github.com/polyfractal/athletic
.. _HOA Bench: http://hoa-project.net/En/Literature/Hack/Bench.html
