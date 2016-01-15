Reports
=======

PHPBench includes some reports by default.

``default``
-----------

The default report presents all of the iteration results on the command line
in the form of a table:

.. code-block:: bash

    +------------------+-------------+---------+--------+------+------+-----+----------+----------+---------+--------+
    | benchmark        | subject     | group   | params | revs | iter | rej | mem      | time     | z-score | diff   |
    +------------------+-------------+---------+--------+------+------+-----+----------+----------+---------+--------+
    | HashingBenchmark | benchMd5    | hashing | []     | 1000 | 0    | 0   | 268,160b | 0.8040μs | -1σ     | -3.48% |
    | HashingBenchmark | benchMd5    | hashing | []     | 1000 | 1    | 0   | 268,160b | 0.8620μs | +1.00σ  | +3.48% |
    | HashingBenchmark | benchSha256 | hashing | []     | 1000 | 0    | 0   | 268,160b | 1.2880μs | +1.00σ  | +1.98% |
    | HashingBenchmark | benchSha256 | hashing | []     | 1000 | 1    | 0   | 268,160b | 1.2380μs | -1σ     | -1.98% |
    | HashingBenchmark | benchSha1   | hashing | []     | 1000 | 0    | 0   | 268,160b | 0.9030μs | -1σ     | -4.7%  |
    | HashingBenchmark | benchSha1   | hashing | []     | 1000 | 1    | 0   | 268,160b | 0.9920μs | +1.00σ  | +4.70% |
    +------------------+-------------+---------+--------+------+------+-----+----------+----------+---------+--------+

Generator: :ref:`generator_table`.

Columns:

- **benchmark**: The name of the class containing the benchmarks
- **subject**: The method which executes the code being benchmarked.
- **group**: The :ref:`group <groups>` the benchmark is in.
- **params**: Any :ref:`parameters` which were passed to the benchmark.
- **revs**: Number of :ref:`revolutions`.
- **iter**: The :ref:`iteration <iterations>` index.
- **rej**: Number of rejected iterations (see :ref:`retry_threshold`).
- **mem**: Average amount of memory used by the iteration.
- **time**: Time taken to execute a single iteration in microseconds_
- **z-score**: `Number of standard deviations`_ this value is away from the mean.
- **diff**: Deviation from the mean as a percentage (when multiple
  benchmarks / iterations are displayed).

``aggregate``
-------------

The aggregate report is similar to the ``default`` report, but shows only one
row for each subject:

.. code-block:: bash

    +------------------+-------------+---------+--------+------+-----+----------+---------+---------+---------+---------+---------+--------+
    | benchmark        | subject     | group   | params | revs | its | mem      | best    | mean    | mode    | worst   | stdev   | rstdev |
    +------------------+-------------+---------+--------+------+-----+----------+---------+---------+---------+---------+---------+--------+
    | HashingBenchmark | benchMd5    | hashing | []     | 1000 | 10  | 272,616b | 2.470μs | 2.636μs | 2.621μs | 2.805μs | 0.093μs | 3.55%  |
    | HashingBenchmark | benchSha1   | hashing | []     | 1000 | 10  | 272,616b | 2.640μs | 2.837μs | 2.903μs | 2.937μs | 0.097μs | 3.43%  |
    | HashingBenchmark | benchSha256 | hashing | []     | 1000 | 10  | 272,616b | 2.735μs | 3.021μs | 2.988μs | 3.247μs | 0.159μs | 5.26%  |
    +------------------+-------------+---------+--------+------+-----+----------+---------+---------+---------+---------+---------+--------+

Generator: :ref:`generator_table`.

Columns:

- **benchmark**: The name of the class containing the benchmarks
- **subject**: The method which executes the code being benchmarked.
- **group**: The :ref:`group <groups>` the benchmark is in.
- **params**: Any :ref:`parameters` which were passed to the benchmark.
- **revs**: Sum of the number of :ref:`revolutions` for all iterations.
- **its**: Number of :ref:`iterations <iterations>` performed.
- **mem**: Average memory used by iteration.
- **best**: Best time; either min (time) or max (throughput)
- **mean**: Average time.
- **mode**: Mode time, the most frequent time according to a kernel density
  estimate.
- **worst**: Worst time; either max (time) or min (throughput)
- **stdev**: The `standard deviation`_.
  benchmarks).
- **rstdev**: `Relative standard deviation`_ as a percentage (standardized
  measure).

.. _report_env:

``env``
-------

This report shows information about the environment that the benchmarks were
executed in.

.. code-block:: bash

    +------------+--------------+---------+------------------------------------------+
    | context    | provider     | key     | value                                    |
    +------------+--------------+---------+------------------------------------------+
    | my_context | uname        | os      | Linux                                    |
    | my_context | uname        | host    | dtlt410                                  |
    | my_context | uname        | release | 4.2.0-1-amd64                            |
    | my_context | uname        | version | #1 SMP Debian 4.2.6-1 (2015-11-10)       |
    | my_context | uname        | machine | x86_64                                   |
    | my_context | php          | version | 5.6.15-1                                 |
    | my_context | unix-sysload | l1      | 0.52                                     |
    | my_context | unix-sysload | l5      | 0.64                                     |
    | my_context | unix-sysload | l15     | 0.57                                     |
    | my_context | vcs          | system  | git                                      |
    | my_context | vcs          | branch  | env_info                                 |
    | my_context | vcs          | version | edde9dc7542cfa8e3ef4da459f0aaa5dfb095109 |
    +------------+--------------+---------+------------------------------------------+

Generator: :ref:`generator_table`.

Columns:

- **context**: Context of the suite (as provided by the ``--context`` option
  of the ``run`` command).
- **provider**: Name of the environment provider (see
  ``PhpBench\\Environment\\Provider`` in the code for more information).
- **key**: Information key.
- **key**: Information value.

See the :doc:`environment` chapter for more information.

.. note::

    The information available will differ depending on platform. For example,
    ``unit-sysload`` is unsurprisingly only available on UNIX platforms, where
    as the VCS field will appear only when a *supported* VCS system is being
    used.

.. _microseconds: https://en.wikipedia.org/wiki/Microseconds
.. _memory_get_peak_usage: http://php.net/manual/en/function.memory-get-peak-usage.php
.. _standard deviation: https://en.wikipedia.org/wiki/Standard_deviation
.. _Relative standard deviation: https://en.wikipedia.org/wiki/Coefficient_of_variation
.. _Number of standard deviations: https://en.wikipedia.org/wiki/Z-score
