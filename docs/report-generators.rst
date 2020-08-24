Report Generators
=================

PHPBench generates reports using report generators. These are classes which
implement the ``PhpBench\Report\GeneratorInterface`` and produce a report XML
document which will later be rendered by using a :doc:`renderer
<report-renderers>` (the ``console`` renderer by default).

This chapter will describe the default report generators.

.. _generator_table:

``table``
---------

The table generator is the main report generator - it is the generator that allows you to analyze your
benchmarking results.

Class: ``PhpBench\Report\Generator\TableGenerator``.

Options:

- **title**: *(string)* Title of the report.
- **description**: *(string)* Description of the report.
- **cols**: *(array)* List of columns to display, see below.
- **break**: *(array)* List of columns; break into multiple tables based on
  specified columns.
- **compare**: *(string)* Isolate and compare values (default ``mean`` time)
  based for the given column.
- **compare_fields**: *(array)* List of fields to compare based on the column
  specified with **compare**.
- **diff_col**: *(string)* If the ``diff`` column is given in ``cols``, use
  this column as the value on which to determine the ``diff`` (default
  ``mean``).
- **sort**: *(assoc_array)* Sort specification, can specify multiple columns;
  e.g. ``{ mean: "asc", benchmark: "desc" }``.
- **pretty_params**: *(boolean)* Pretty print the ``params`` field.
- **iterations**: *(boolean)* Include the results of every individual
  iteration (default ``false``).
- **labels**: *(array)* Override the default column names, either as a
  numerical array or as a `colName => label` hash.
- **baseline**: Use any previous suites as baselines for the most recent
  suite.
- **baseline_fields**: Change the default selection of fields to which
  baseline results are shown.

.. _generator_table_columns:

Columns
~~~~~~~

Here we divide the columns into three sets, *conditions* are those columns
which determine the execution context, *variant statistics* are aggregate
statistics relating to a set of iterations and *iteration statistcs* relate to
single iterations (as provided when ``iterations`` option is set to ``true``).

Conditions:

- **suite**: Identifier of the suite.
- **tag**: If applicable, the tag which was applied to this suite.
- **date**: Date the suite was generated,
- **stime**: Time the suite was generated 
- **benchmark**: Short name of the benchmark class (i.e. no namespace).
- **benchmark_full**: Fully expanded name of benchmark class.
- **subject**: Name of the subject method.
- **groups**: Comma separated list of groups.
- **params**: Parameters (represented as JSON).
- **revs**: Number of revolutions.
- **its**: Number of iterations.

Variant Statistics:

- **mem_peak**: (mean) Peak memory used by each iteration as retrieved by memory_get_peak_usage_.
- **mem_final**: (mean) Memory allocated to PHP at the end of the benchmark
  (`memory_get_usage`).
- **mem_real**: (mean) Memory allocated by the system for PHP at the end of the benchmark (`memory_get_usage(true)`).
- **min**: Minimum time of all iterations in variant.
- **max**: Maximum time of all iterations in variant.
- **worst**: Synonym for ``max``.
- **best**: Synonym for ``min``.
- **sum**: Total time taken by all iterations in variant,
- **stdev**: `Standard deviation`_
- **mean**: Mean time taken by all iterations in variant.
- **mode**: Mode_ of all iterations in variant.
- **variance**: The variance_ of the variant.
- **rstdev**: The `relative standard deviation`_.

Iteration Statistics:

- **mem_peak**: Peak memory used by each iteration as retrieved by memory_get_peak_usage_.
- **mem_final**: Memory allocated to PHP at the end of the benchmark
  (`memory_get_usage`).
- **mem_real**: Memory allocated by the system for PHP at the end of the benchmark (`memory_get_usage(true)`).
- **iter**: Index of iteration.
- **rej**: Number of rejections the iteration went through (see
  :ref:`retry_threshold`.
- **time_net**: Time in (microseconds_) it took for the iteration to complete.
- **time_rev**: Time per revolution (``time_net / nb revs``).
- **z-vaue**: The `number of standard deviations`_ away from the mean of the
  iteration set (the variant).

In addition any number of environment columns are added in the form of
``<provider_name>_<key>``, so for example the column for the VCS branch would
be ``vcs_branch``.

``composite``
-------------

This report generates multiple reports.

Class: ``PhpBench\Report\Generator\CompositeGenerator``.

Options:

- **reports**: *(array)*: List of report names.

``env``
-------

This is a simple generator which generates a report listing all of the
environmental factors for each suite.

Class: ``PhpBench\Report\Generator\EnvGenerator``.

Options:

- **title**: *(string)* Title of the report.
- **description**: *(string)* Description of the report.

.. _Standard deviation: https://en.wikipedia.org/wiki/Standard_deviation
.. _variance: https://en.wikipedia.org/wiki/Variance
.. _relative standard deviation: https://en.wikipedia.org/wiki/Coefficient_of_variation
.. _number of standard deviations: https://en.wikipedia.org/wiki/Z-score
.. _Mode: https://en.wikipedia.org/wiki/Mode_(statistics)
.. _microseconds: https://en.wikipedia.org/wiki/Microseconds
.. _memory_get_peak_usage: http://php.net/manual/en/function.memory-get-peak-usage.php
