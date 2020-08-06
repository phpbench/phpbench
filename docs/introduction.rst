Introduction
============

PHPBench is a benchmark runner for PHP. It enables you to write standard
benchmarks for your application and classes and helps you to make smart
decisions based on *comparative* results.

Features at a glance:

- **Revolutions and Iterations**: Spin and repeat.
- **Process Isolation**: Benchmarks are run in separate processes with no
  significant overhead from the runner.
- **Reporting**: Powerful and extensible reports.
- **Deferred Reporting**: Dump benchmarking results to an XML file and report
  on them later.
- **Memory Usage**: Keep an eye on the amount of memory used by benchmarking
  subjects.

Why PHPBench?
-------------

Performance can be monitored and measured in a number of ways: profiling (via.
`Xdebug`_ or `Blackfire`_), injecting timing classes (e.g. `Symfony Stopwatch`_, `Hoa
Bench`_) or with server tools such as `NewRelic`_.

PHPBench differs from these tools in that it allows you to benchmark explicit
scenarios independently of the application context, and to run these scenarios
multiple times in order to obtain a degree of *confidence* about the stability
of the results.

As a tool it is analogous to the test framework `PHPUnit`_, but instead of *tests* we run
*benchmarks* and generate reports.

.. _Symfony Stopwatch: http://symfony.com/doc/current/components/stopwatch.html
.. _Xdebug: http://xdebug.org
.. _Blackfire: https://blackfire.io/
.. _NewRelic: http://newrelic.com
.. _HOA Bench: http://hoa-project.net/En/Literature/Hack/Bench.html
.. _PHPunit: http://phpunit.de
