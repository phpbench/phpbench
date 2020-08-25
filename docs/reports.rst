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
data, either from XML dumps or by using a :doc:`storage <storage>` UUID or tag::

    $ phpbench report --uuid=latest --report=aggregate

For more information on storage see :doc:`storage <storage>`.

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

You can show the percentage of difference from the lowest column value in the table (:math:`($meanOrMode / $min)  - 1) * 100`) by specifying the ``diff`` column. By
default this will use the ``mean``, you can specify a different value using the ``diff_col`` option, e.g. ``diff_col: "mode"``.

.. code-block:: bash

    $ phpbench run --report='generator: "table", cols: ["subject", "revs", "mean", "diff"]'
    +---------------+------+--------+---------+
    | subject       | revs | mean   | diff    |
    +---------------+------+--------+---------+
    | benchVariance | 100  | 6.73μs | 0.00%   |
    | benchStDev    | 100  | 8.11μs | +20.39% |
    +---------------+------+--------+---------+

Sorting
~~~~~~~

Sorting can be achieved on multiple columns in either ascending (``asc``) or descending (``desc``) order.

.. code-block:: bash

    $ phpbench run --report='generator: "table", cols: ["subject", "revs", "mean", "diff"], sort: {subject: "asc", mean: "desc"}'


Default Reports
---------------

Configured reports can be executed simply by name as follows::

    $ phpbench run --report=aggregate

The following are reports defined by PHPBench, other reports can be defined in your :doc:`configuration <configuration>`.

``aggregate``
~~~~~~~~~~~~~

Shows aggregate details of each set of iterations:

.. code-block:: bash

    +--------------+-------------+--------+--------+------+-----+------------+---------+---------+---------+---------+---------+--------+-------+
    | benchmark    | subject     | groups | params | revs | its | mem_peak   | best    | mean    | mode    | worst   | stdev   | rstdev | diff  |
    +--------------+-------------+--------+--------+------+-----+------------+---------+---------+---------+---------+---------+--------+-------+
    | HashingBench | benchMd5    |        | []     | 1000 | 10  | 1,255,792b | 0.931μs | 0.979μs | 0.957μs | 1.153μs | 0.062μs | 6.37%  | 1.00x |
    | HashingBench | benchSha1   |        | []     | 1000 | 10  | 1,255,792b | 0.988μs | 1.015μs | 1.004μs | 1.079μs | 0.026μs | 2.57%  | 1.04x |
    | HashingBench | benchSha256 |        | []     | 1000 | 10  | 1,255,792b | 1.273μs | 1.413μs | 1.294μs | 1.994μs | 0.242μs | 17.16% | 1.44x |
    +--------------+-------------+--------+--------+------+-----+------------+---------+---------+---------+---------+---------+--------+-------+

It is uses the ``table`` generator, see :ref:`generator_table` for more information.


``default``
~~~~~~~~~~~

The default report presents the result of *each iteration*:

.. code-block:: bash

    -------------+-------------+--------+--------+------+------+------------+----------+--------------+----------------+
    | benchmark    | subject     | groups | params | revs | iter | mem_peak   | time_rev | comp_z_value | comp_deviation |
    +--------------+-------------+--------+--------+------+------+------------+----------+--------------+----------------+
    | HashingBench | benchMd5    |        | []     | 1000 | 0    | 1,255,792b | 0.985μs  | +1.00σ       | +0.20%         |
    | HashingBench | benchMd5    |        | []     | 1000 | 1    | 1,255,792b | 0.981μs  | -1σ          | -0.2%          |
    | HashingBench | benchSha1   |        | []     | 1000 | 0    | 1,255,792b | 0.992μs  | +1.00σ       | +0.05%         |
    | HashingBench | benchSha1   |        | []     | 1000 | 1    | 1,255,792b | 0.991μs  | -1σ          | -0.05%         |
    | HashingBench | benchSha256 |        | []     | 1000 | 0    | 1,255,792b | 1.533μs  | +1.00σ       | +8.68%         |
    | HashingBench | benchSha256 |        | []     | 1000 | 1    | 1,255,792b | 1.288μs  | -1σ          | -8.68%         |
    +--------------+-------------+--------+--------+------+------+------------+----------+--------------+----------------+

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
