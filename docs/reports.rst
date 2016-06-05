Reports
=======

PHPBench includes a primitive reporting framework. It allows for :doc:`report
generators <report-generators>` which generate *reports* from one or more
benchmarking suite results.

Reports can be generated for each ``run`` that your perform, or using
historical data by using the ``report`` command.

The reports are then *renderered* using a :doc:`report renderer
<report-renderers>` to various outputs (e.g. console, HTML, markdown, CSV).

This chapter will deal with generating reports and assume that the ``console``
renderer is used.

Generating Reports
------------------

To report after a benchmarking run::

    $ phpbench run --report=aggregate

Multiple reports can be specified::

    $ phpbench run --report=aggregate --report=env

The report command operates in a similar way but requires you to provide some
data, either from XML dumps or from a :doc:`storage <storage>` query::

    $ phpbench report --query='benchmark: "MyBench"' --report=aggregate

For more information on storage and the query language see :doc:`storage`.

Configuring Reports
-------------------

All reports can be configured either in the :ref:`report configuration
<configuration_reports>` or directly on the command line using a simplified
JSON encoded string instead of the report name::

   $ phpbench run --report='generator: "table", cols: [ "suite", "subject", "mean" ], break: ["benchmark"]'

In each case it is required to specify the ``generator`` key which corresponds
to the registered name of the :doc:`report generator <report-generators>`.

You may also **extend** an existing report configuration::

   $ phpbench run --report='extend: "aggregate", break: ["benchmark", "revs"]'

This will merge the given keys onto the configuration for the `aggregate
report`_.

Table Generator
---------------

For details about the table generator see the :ref:`generator_table`
reference, this section will simply offer practical examples.

.. note::

    Here we give the report configuration as an argument on the command line,
    it is important to note that reports can also be defined in the
    :doc:`configuration <configuration>`.

Selecting columns
~~~~~~~~~~~~~~~~~

You can select exactly which columns you need using the ``cols`` option. If you make a mistake an exception
will be thrown showing all the valid possibilities, see the :ref:`columns <generator_table_columns>` reference.

The following examples will make use of this option for brevity.

Breaking into multiple tables
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Use the ``break`` option to split tables based on the unique values of the
given keys:

.. code-block:: bash

    $ phpbench run --report='generator: "table", break: ["revs"], cols: ["subject", "mean"]'

	revs: 1
	+-------------+---------+
	| subject     | mean    |
	+-------------+---------+
	| benchMd5    | 3.300μs |
	| ...         | ...     |
	+-------------+---------+

	revs: 10
	+-------------+---------+
	| subject     | mean    |
	+-------------+---------+
	| benchMd5    | 0.700μs |
	| ...         | ...     |
	+-------------+---------+

	revs: 100
	+-------------+---------+
	| subject     | mean    |
	+-------------+---------+
	| benchMd5    | 0.447μs |
	| ...         | ...     |
	+-------------+---------+

Multiple columns may be specified:

.. code-block:: bash

    $ phpbench run --report='generator: "table", break: ["benchmark", "revs"], cols: ["subject", "mean"]'

    benchmark: HashingBenchmark, revs: 1
    +-------------+---------+
    | subject     | mean    |
    +-------------+---------+
    | benchMd5    | 3.400μs |
    | benchSha1   | 4.700μs |
    | benchSha256 | 4.700μs |
    +-------------+---------+

    benchmark: HashingBenchmark, revs: 10
    +-------------+---------+
    | subject     | mean    |
    +-------------+---------+
    | benchMd5    | 0.720μs |
    | benchSha1   | 0.970μs |
    | benchSha256 | 1.320μs |
    +-------------+---------+


Comparing Values
~~~~~~~~~~~~~~~~

To compare values by factor horizontally, use the ``compare`` option, for example to compare mean times against revs:

.. code-block:: bash

    $ phpbench run --report='generator: "table", compare: "revs", cols: ["subject", "mean"]'

	+-------------+-------------+--------------+---------------+
	| subject     | revs:1:mean | revs:10:mean | revs:100:mean |
	+-------------+-------------+--------------+---------------+
	| benchMd5    | 3.800μs     | 0.890μs      | 0.535μs       |
	| benchSha1   | 5.600μs     | 0.930μs      | 0.651μs       |
	| benchSha256 | 5.500μs     | 1.490μs      | 1.114μs       |
	+-------------+-------------+--------------+---------------+

By default the mean is used as the comparison value, you may also select different value columns using ``compare_fields``, e.g. to show both ``mean`` and ``mode``:

.. code-block:: bash

    $ phpbench run --report='generator: "table", compare: "revs", cols: ["subject", "mean"], compare_fields: ["mean", "mode"]'

.. note::

    The compare function "squashes" the non-statistical columns which have the same
    values - sometimes this may result in there being more than one "statstic"
    for the ``compare`` column. In such cases extra columns are added suffixed
    with an index, for example: ``revs:10:mean#1``.

Difference Between Rows
~~~~~~~~~~~~~~~~~~~~~~~

You can show the percentage of difference from the lowest column value in the table by specifying the ``diff`` column. By
default this will use the ``mean``, you can specify a different value using the ``deviation_col`` option, e.g. ``deviation_col: "mode"``.

.. code-block:: bash

    $ phpbench run --report='generator: "table", cols: ["subject", "revs", "mean", "diff"]'

	+-------------+------+---------+---------+
	| subject     | revs | mean    | diff    |
	+-------------+------+---------+---------+
	| benchMd5    | 100  | 0.400μs | 0.00%   |
	| benchSha1   | 100  | 0.497μs | +19.52% |
	| benchSha256 | 100  | 0.886μs | +54.85% |
	+-------------+------+---------+---------+

Sorting
~~~~~~~

Sorting can be achieved on multiple columns in either ascending (``asc``) or descending (``desc``) order.

.. code-block:: bash

    $ phpbench run --report='generator: "table", cols: ["subject", "revs", "mean", "diff"], sort: {subject: "asc", mean: "desc"}'

	+-------------+------+---------+---------+
	| subject     | revs | mean    | diff    |
	+-------------+------+---------+---------+
	| benchMd5    | 1    | 3.600μs | +89.32% |
	| benchMd5    | 10   | 0.680μs | +43.44% |
	| benchMd5    | 100  | 0.420μs | +8.43%  |
	| benchSha1   | 1    | 5.000μs | +92.31% |
	| benchSha1   | 10   | 0.900μs | +57.27% |
	| benchSha1   | 100  | 0.494μs | +22.15% |
	| benchSha256 | 1    | 4.600μs | +91.64% |
	| benchSha256 | 10   | 1.320μs | +70.86% |
	| benchSha256 | 100  | 0.847μs | +54.59% |
	+-------------+------+---------+---------+

Default Reports
---------------

Configured reports can be executed simply by name as follows::

    $ phpbench run --report=aggregate

The following are reports defined by PHPBench, other reports can be defined in your :doc:`configuration <configuration>`.

``aggregate``
~~~~~~~~~~~~~

Shows aggregate details of each each set of iterations:

.. code-block:: bash

    +------------------+-------------+---------+--------+------+-----+----------+---------+---------+---------+---------+---------+--------+
    | benchmark        | subject     | groups  | params | revs | its | mem_peak | best    | mean    | mode    | worst   | stdev   | rstdev |
    +------------------+-------------+---------+--------+------+-----+----------+---------+---------+---------+---------+---------+--------+
    | HashingBenchmark | benchMd5    | hashing | []     | 1000 | 10  | 272,616b | 2.470μs | 2.636μs | 2.621μs | 2.805μs | 0.093μs | 3.55%  |
    | HashingBenchmark | benchSha1   | hashing | []     | 1000 | 10  | 272,616b | 2.640μs | 2.837μs | 2.903μs | 2.937μs | 0.097μs | 3.43%  |
    | HashingBenchmark | benchSha256 | hashing | []     | 1000 | 10  | 272,616b | 2.735μs | 3.021μs | 2.988μs | 3.247μs | 0.159μs | 5.26%  |
    +------------------+-------------+---------+--------+------+-----+----------+---------+---------+---------+---------+---------+--------+

It is uses the ``table`` generator, see :ref:`generator_table` for more information.


``default``
~~~~~~~~~~~

The default report presents the result of *each iteration*:

.. code-block:: bash

    +------------------+-------------+---------+--------+------+------+-----+----------+----------+---------+--------+
    | benchmark        | subject     | groups  | params | revs | iter | rej | mem_peak | time     | z-score | diff   |
    +------------------+-------------+---------+--------+------+------+-----+----------+----------+---------+--------+
    | HashingBenchmark | benchMd5    | hashing | []     | 1000 | 0    | 0   | 268,160b | 0.8040μs | -1σ     | -3.48% |
    | HashingBenchmark | benchMd5    | hashing | []     | 1000 | 1    | 0   | 268,160b | 0.8620μs | +1.00σ  | +3.48% |
    | HashingBenchmark | benchSha256 | hashing | []     | 1000 | 0    | 0   | 268,160b | 1.2880μs | +1.00σ  | +1.98% |
    | HashingBenchmark | benchSha256 | hashing | []     | 1000 | 1    | 0   | 268,160b | 1.2380μs | -1σ     | -1.98% |
    | HashingBenchmark | benchSha1   | hashing | []     | 1000 | 0    | 0   | 268,160b | 0.9030μs | -1σ     | -4.7%  |
    | HashingBenchmark | benchSha1   | hashing | []     | 1000 | 1    | 0   | 268,160b | 0.9920μs | +1.00σ  | +4.70% |
    +------------------+-------------+---------+--------+------+------+-----+----------+----------+---------+--------+

It is uses the ``table`` generator, see :ref:`generator_table` for more information.

.. _report_env:

``env``
~~~~~~~

This report shows information about the environment that the benchmarks were
executed in.

.. code-block:: bash

    +--------------+---------+------------------------------------------+
    | provider     | key     | value                                    |
    +--------------+---------+------------------------------------------+
    | uname        | os      | Linux                                    |
    | uname        | host    | dtlt410                                  |
    | uname        | release | 4.2.0-1-amd64                            |
    | uname        | version | #1 SMP Debian 4.2.6-1 (2015-11-10)       |
    | uname        | machine | x86_64                                   |
    | php          | version | 5.6.15-1                                 |
    | unix-sysload | l1      | 0.52                                     |
    | unix-sysload | l5      | 0.64                                     |
    | unix-sysload | l15     | 0.57                                     |
    | vcs          | system  | git                                      |
    | vcs          | branch  | env_info                                 |
    | vcs          | version | edde9dc7542cfa8e3ef4da459f0aaa5dfb095109 |
    +--------------+---------+------------------------------------------+

Generator: :ref:`generator_table`.

Columns:

- **provider**: Name of the environment provider (see
  ``PhpBench\\Environment\\Provider`` in the code for more information).
- **key**: Information key.
- **value**: Information value.

See the :doc:`environment` chapter for more information.

.. note::

    The information available will differ depending on platform. For example,
    ``unit-sysload`` is unsurprisingly only available on UNIX platforms, where
    as the VCS field will appear only when a *supported* VCS system is being
    used.

.. _aggregate report: https://github.com/phpbench/phpbench/blob/master/lib/Extension/config/report/generators.php
