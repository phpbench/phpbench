Reports
=======

PHPBench includes some reports by default.

``default``
-----------

The default report presents all of the iteration results on the command line
in the form of a table:

.. code-block:: bash

    +-------------------+--------------+-------+--------+------+--------------+----------+--------+-----------+
    | benchmark         | subject      | group | params | revs | iter         | time     | memory | deviation |
    +-------------------+--------------+-------+--------+------+--------------+----------+--------+-----------+
    | TimeConsumerBench | benchConsume |       | []     | 1    | 0            | 226.00μs | 3,416b | 0.00%     |
    |                   |              |       |        |      |              |          |        |           |
    |                   |              |       |        |      | stability >> | 100.00%  |        |           |
    |                   |              |       |        |      | average >>   | 226.00μs | 3,416b |           |
    +-------------------+--------------+-------+--------+------+--------------+----------+--------+-----------+

Generator: :ref:`generator_table`.

Columns:

- **benchmark**: The name of the class contianing the benchmarks
- **subject**: The method which executes the code being benchmarked.
- **group**: The :ref:`group <groups>` the benchmark is in.
- **params**: Any :ref:`parameters` which were passed to the benchmark.
- **revs**: Number of :ref:`revolutions`.
- **iter**: The :ref:`iteration <iterations>` index.
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

    +-------------------+--------------+-------+--------+------+-------+------------+--------+-----------+-----------+
    | benchmark         | subject      | group | params | revs | iters | time       | memory | deviation | stability |
    +-------------------+--------------+-------+--------+------+-------+------------+--------+-----------+-----------+
    | TimeConsumerBench | benchConsume |       | []     | 1    | 1     | 227.0000μs | 3,416b | 0.00%     | 100.00%   |
    +-------------------+--------------+-------+--------+------+-------+------------+--------+-----------+-----------+

Generator: :ref:`generator_table`.

Columns:

- **benchmark**: The name of the class contianing the benchmarks
- **subject**: The method which executes the code being benchmarked.
- **group**: The :ref:`group <groups>` the benchmark is in.
- **params**: Any :ref:`parameters` which were passed to the benchmark.
- **revs**: Sum of the number of :ref:`revolutions` for all iterations.
- **iters**: Number of :ref:`iterations <iterations>` performed.
- **time**: Average time taken for each iteration.
- **memory**: Average memory used.
- **deviation**: Deviation from the mean as a percentage (when multiple
  benchmarks).
- **stability**: How much the time of the individual iterations differed.

.. _microseconds: https://en.wikipedia.org/wiki/Microseconds
