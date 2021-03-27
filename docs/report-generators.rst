Report Generators
=================

PHPBench generates reports using report generators. These are classes which
implement the ``PhpBench\Report\GeneratorInterface`` and produce a report XML
document which will later be rendered by using a :doc:`renderer
<report-renderers>` (the ``console`` renderer by default).

This chapter will describe the default report generators.

.. _generator_table:

``expression``
--------------

The table generator is the main report generator - it is the generator that allows you to analyze your
benchmarking results.

Class: ``PhpBench\Report\Generator\TableGenerator``.

Options:

- **title**: *(string)* Title of the report.
- **description**: *(string)* Description of the report.
- **cols**: *(array)* List of columns/expressions to show
- **expressions**: *(array)* Set of available expressions
- **baseline_expressions**: *(array)* Set of expressions that will be used
  when a baseline is present
- **break**: *(array)* List of columns; break into multiple tables based on
- **aggregate**: *(array)* List of fields to aggregate on.

.. _generator_table_columns:

Columns
~~~~~~~

The visible columns are dicated by the ``cols`` configuration, you can also
override or set sessions:

.. code-block:: javascript

    {
        "cols": {
            "subject",
            "mode",
            "foobar": "\"Hello\""
        }
    }

The above will only show the default columns "subject" and "mode" but will
also add a new column with an :ref:`expression <expression_language>` which evaluates to the string ``Hello``.

Data
----

TODO

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
