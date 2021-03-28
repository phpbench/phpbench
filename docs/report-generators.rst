Report Generators
=================

PHPBench generates reports using report generators. These are classes which
implement the ``PhpBench\Report\GeneratorInterface`` and produce a report XML
document which will later be rendered by using a :doc:`renderer
<report-renderers>` (the ``console`` renderer by default).

This chapter will describe the default report generators.

.. _generator_expression:

``expression``
--------------

The expression generator is the main report generator - it is the generator that allows you to analyze your
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

.. _generator_expression_columns:

Columns
~~~~~~~

The visible columns are dicated by the ``cols`` configuration, you can also
override or set sessions:

.. phpbench:: ../examples/Command/report-generators-columns
  :section: config

The above will only show the default columns "subject" and "mode" but will
also add a new column with an :ref:`expression <expression_language>` which evaluates to the string ``Hello``.

.. phpbench:: ../examples/Command/report-generators-columns
  :language: bash
  :section: command

.. phpbench:: ../examples/Command/report-generators-columns
  :section: output

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
