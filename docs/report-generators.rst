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
benchmarking results:

.. approved:: ../examples/Command/report-generators-expression
  :language: javascript
  :section: 1

Yields something like:

.. approved:: ../examples/Command/report-generators-expression
  :language: javascript
  :section: 2

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

The visible columns are dicated by the ``cols`` configuration:

.. approved:: ../examples/Command/report-generators-column-visibility
  :language: javascript
  :section: 0

When using the report:

.. approved:: ../examples/Command/report-generators-column-visibility
  :language: shell
  :section: 1

It will only show the selected columns:

.. approved:: ../examples/Command/report-generators-column-visibility
  :language: bash
  :section: 2

You can also override expressions by passing a map:

.. approved:: ../examples/Command/report-generators-column-override
  :language: javascript
  :section: 0

Which yields:

.. approved:: ../examples/Command/report-generators-column-override
  :language: bash
  :section: 2

.. _generator_expression_break:

Break
~~~~~

You can split the report into mupltiple tables by using the ``break`` option:

.. approved:: ../examples/Command/report-generators-break
  :language: javascript
  :section: 0

Now each benchmark class will get its own table:

.. approved:: ../examples/Command/report-generators-break
  :language: bash
  :section: 2

Note that this also reduces the length of the table.

You can specify multiple columns.

.. _generator_expression_expressions:

Expressions
-----------

The expressions define the available columns:

.. approved:: ../examples/Command/report-generators-expressions
  :language: javascript
  :section: 0

Which yields:

.. approved:: ../examples/Command/report-generators-expressions
  :language: bash
  :section: 2

In general you will not modify this, but instead use
:ref:`generator_expression_columns` instead to merge new columns into the
default ones:

Data
----

The expressions act on table data. You can get a list of all available columns
with:

.. approved:: ../examples/Command/report-generators-data
  :language: bash
  :section: 1

Yielding:

.. approved:: ../examples/Command/report-generators-data
  :language: bash
  :section: 2

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
