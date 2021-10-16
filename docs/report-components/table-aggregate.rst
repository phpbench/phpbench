Table Aggregate
===============

Render a table with aggregated data.

.. figure:: ../images/table_aggregate.png
   :alt: HTML output

   HTML output

Options
-------

.. include:: options/_table_aggregate.rst

Example
-------

Given the following configuration:

.. approved:: ../../examples/Command/report-component-table-aggregate
  :language: bash
  :section: 0

When we run PHPBench with the configured report above:


.. approved:: ../../examples/Command/report-component-table-aggregate
  :language: bash
  :section: 1

Then it generates the following with the ``console`` renderer:

.. approved:: ../../examples/Command/report-component-table-aggregate
  :language: bash
  :section: 2

Advanced Columns
----------------

More advanced behavior can be accessed by passing an object instead
of an expression, specifying a "column processor" ``type``.

``expand``
~~~~~~~~~~

You can "expand" columns dynamically by iterating over an expression using the
``expand`` processor:

.. approved:: ../../examples/Command/report-component-table-aggregate-expand
  :language: bash
  :section: 0

Above we:

- Use an object as the value of the column definition
- Specified the ``type`` to be ``expand``
- Specify an expression to iterate over (``parition[iteration_index]``)
- Specify a set of columns
- We filter the partition using the value from the iterated value

Note that:

- The ``item`` variable is available in the column label definition and it's
  expression. The name of this variable can be changed using the ``param``
  option.
- The when specifying the ``item`` in a data frame filter (``partition[foo =
  _params.item]``) we prefix ``item`` with ``_params`` in order to access the
  params passed to the expression rather than params available to the filter.

Which would produce:

.. approved:: ../../examples/Command/report-component-table-aggregate-expand
  :language: bash
  :section: 2
