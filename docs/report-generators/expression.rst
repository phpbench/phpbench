.. _generator_expression:

Expression Report
=================

The expression generator is the generator that allows you to analyze your
benchmarking results. It uses the PHPBench :doc:`expression language
<expression>` to evaluate tabular data:

.. approved:: ../../examples/Command/report-generators-expression
  :language: javascript
  :section: 1

Yields something like:

.. approved:: ../../examples/Command/report-generators-expression
  :language: bash
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
- **include_baseline**: *(bool)* Include the baseline rows

.. _generator_expression_columns:

Columns
-------

The visible columns are dicated by the ``cols`` configuration:

.. approved:: ../../examples/Command/report-generators-column-visibility
  :language: javascript
  :section: 0

When using the report:

.. approved:: ../../examples/Command/report-generators-column-visibility
  :language: shell
  :section: 1

It will only show the selected columns:

.. approved:: ../../examples/Command/report-generators-column-visibility
  :language: bash
  :section: 2

You can also override expressions by passing a map:

.. approved:: ../../examples/Command/report-generators-column-override
  :language: javascript
  :section: 0

Which yields:

.. approved:: ../../examples/Command/report-generators-column-override
  :language: bash
  :section: 2

.. _generator_expression_break:

Break
-----

You can partition the report into mupltiple tables by using the ``break`` option:

.. approved:: ../../examples/Command/report-generators-break
  :language: javascript
  :section: 0

Now each benchmark class will get its own table:

.. approved:: ../../examples/Command/report-generators-break
  :language: bash
  :section: 2

.. _generator_expression_expressions:

Expressions
-----------

The expressions define the available columns, you can add or override
expressions:

.. approved:: ../../examples/Command/report-generators-expressions
  :language: javascript
  :section: 0

Which yields:

.. approved:: ../../examples/Command/report-generators-expressions
  :language: bash
  :section: 2

Data
----

The expressions act on table data. You can get a list of all available columns
with:

.. approved:: ../../examples/Command/report-generators-data
  :language: bash
  :section: 1

Yielding:

.. approved:: ../../examples/Command/report-generators-data
  :language: bash
  :section: 2

Note that any additional result and :doc:`environment <../environment>` data will
also be included in the form `result_<type>_<metric>` and
`env_<type>_<metric>`.
