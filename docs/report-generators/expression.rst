.. _generator_expression:

Expression Report
=================

.. note:: 

    For custom reports it is now recommended to use the :doc:`component` generator.

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

Options
-------

.. include:: options/_expression.rst

.. _generator_expression_columns:

Columns
-------

The visible columns are dictated by the ``cols`` configuration:

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

.. _generator_expression_aggregate:

Aggregate
---------

Aggregation decides which values are included in each row - should each row
contain only the values for a single iteration? should all values for the
variant by included? should we include all values for the entire suite? (not
recommended).

.. approved:: ../../examples/Command/report-generators-aggregate
  :language: javascript
  :section: 0

This will aggregate by unique values of the named columns, producing a single
row per iteration:

.. approved:: ../../examples/Command/report-generators-aggregate
  :language: bash
  :section: 2

.. _generator_expression_break:

Break
-----

You can partition the report into multiple tables by using the ``break`` option:

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

The expressions have access to all :ref:`aggregated
<generator_expression_aggregate>` data, and in addition, the
entire result set via. the ``suite`` variable.

The :ref:`Aggregated <generator_expression_aggregate>` data is provided as an array
of column names to values:

.. code-block:: text

    {
        // ...
        "subject_name": ["benchFoobar", "benchFoobar", "benchFoobar"],
        "result_time_net": [10, 20, 30],
        // ...
    }

So the ``mode`` for ``result_time_net`` could be calculated via the expression
``mode(result_time_net)``.

The ``suite`` variable is data frame that represents the entire result set and
can be used to access a specific value through :ref:`filtering
<expr_filtering>`. In the contrived example below we calculate the difference
between the mode of a referenced subject against that of the current variant:

.. approved:: ../../examples/Command/report-generators-filter-value
  :language: bash
  :section: 0

Yielding:

.. approved:: ../../examples/Command/report-generators-filter-value
  :language: bash
  :section: 2


You can get a list of all available columns
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
