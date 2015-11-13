Reports
=======

PHPBench includes some reports by default.

``default``
-----------

The default report presents all of the iteration results on the command line
in the form of a table:

.. code-block:: bash

    +-------------------+--------------+-------+--------+------+------+--------------+------+----------+--------+-----------+
    | benchmark         | subject      | group | params | revs | con  | iter         | rej  | time     | memory | deviation |
    +-------------------+--------------+-------+--------+------+------+--------------+------+----------+--------+-----------+
    | TimeConsumerBench | benchConsume |       | []     | 1    | 1    | 0            | 0    | 226.00μs | 3,416b | 0.00%     |
    |                   |              |       |        |      |      |              |      |          |        |           |
    |                   |              |       |        |      |      | stability >> |      | 100.00%  |        |           |
    |                   |              |       |        |      |      | average >>   | 0.00 | 226.00μs | 3,416b |           |
    +-------------------+--------------+-------+--------+------+------+--------------+------+----------+--------+-----------+

Generator: :ref:`generator_table`.

Columns:

- **benchmark**: The name of the class contianing the benchmarks
- **subject**: The method which executes the code being benchmarked.
- **group**: The :ref:`group <groups>` the benchmark is in.
- **params**: Any :ref:`parameters` which were passed to the benchmark.
- **revs**: Number of :ref:`revolutions`.
- **con**: Number of :ref:`concurrent iterations <concurrency>` that were permitted.
- **iter**: The :ref:`iteration <iterations>` index.
- **rej**: Number of rejected iterations (see :ref:`retry_threshold`).
- **time**: Time taken to execute a single iteration in microseconds_
- **memory**: Memory used, in bytes.
- **deviation**: Deviation from the mean as a percentage (when multiple
  benchmarks / iterations are displayed).

Footers:

- **stability**: 100% stability is obtained when all of the rows have the same
  time.
- **average**: Average time and memory for all rows.

``aggregate``
-------------

The aggregate report is similar to the ``default`` report, but shows only one
row for each subject:

.. code-block:: bash

    +-------------------+--------------+-------+--------+------+------+-------+-----+------------+--------+-----------+-----------+
    | benchmark         | subject      | group | params | revs | con  | iters | rej | time       | memory | deviation | stability |
    +-------------------+--------------+-------+--------+------+------+-------+-----+------------+--------+-----------+-----------+
    | TimeConsumerBench | benchConsume |       | []     | 1    | 1    | 1     | 0   | 227.0000μs | 3,416b | 0.00%     | 100.00%   |
    +-------------------+--------------+-------+--------+------+------+-------+-----+-------+--------+-----------+-----------+

Generator: :ref:`generator_table`.

Columns:

- **benchmark**: The name of the class contianing the benchmarks
- **subject**: The method which executes the code being benchmarked.
- **group**: The :ref:`group <groups>` the benchmark is in.
- **params**: Any :ref:`parameters` which were passed to the benchmark.
- **revs**: Sum of the number of :ref:`revolutions` for all iterations.
- **con**: Number of :ref:`concurrent iterations <concurrency>` that were permitted.
- **iters**: Number of :ref:`iterations <iterations>` performed.
- **rej**: Number of rejected iterations (see :ref:`retry_threshold`).
- **time**: Average time taken for each iteration.
- **memory**: Average memory used.
- **deviation**: Deviation from the mean as a percentage (when multiple
  benchmarks).
- **stability**: How much the time of the individual iterations differed.

.. _microseconds: https://en.wikipedia.org/wiki/Microseconds
