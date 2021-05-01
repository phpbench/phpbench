Introduction
============

PHPBench is a benchmark runner for PHP analagous to `PHPUnit`_ but for
performance rather than correctness.

Performance can be monitored and measured in a number of ways: profiling
(via. `Xdebug`_ or `Blackfire`_), manually instrumenting your code
(e.g. `Symfony Stopwatch`_) or with APM tools such as `NewRelic`_ or `Tideways`_.

PHPBench compliments these tools (and in some cases can integrate with them)
allowing you to profile the `wall time`_ and memory usage of specific
scenarios and code units independently of the application context.

Features include but are not limited to:

- :ref:`Revolutions <metadata_revolutions>`: Repeat your code many times to determine average execution
  *time*.
- :ref:`Iterations <metadata_iterations>`: Sample your revolutions many times and review aggregated
  statistical data.
- **Process Isolation**: Each iteration is executed in a separate process.
- :doc:`Reporting <guides/reports>`: Customizable reports and various output formats (e.g.
  console, CSV, Markdown, HTML).
- Report :doc:`guides/storage` and :ref:`comparison <comparison>`: Store benchmarks locally to be used as a
  :ref:`baseline <baseline>` reference, or to reference them later.
- **Memory Usage**: Keep an eye on the amount of memory used by benchmarking
  subjects.
- :doc:`Assertions <guides/assertions>`: Assert that code is performing within acceptable limits, or
  that it has not regressed from a previously recorded :ref:`baseline <baseline>`.

.. _wall time: https://en.wikipedia.org/wiki/Elapsed_real_time
.. _Symfony Stopwatch: http://symfony.com/doc/current/components/stopwatch.html
.. _Xdebug: http://xdebug.org
.. _Blackfire: https://blackfire.io/
.. _NewRelic: http://newrelic.com
.. _Tideways: https://tideways.com/
.. _PHPUnit: http://phpunit.de
