Bar Chart Aggregate
===================

Render generate a bar chart from aggregated values

.. figure:: ../images/barchart_aggregate.png
   :alt: HTML output

   HTML output

Options
-------

.. include:: options/_bar_chart_aggregate.rst

Example
-------

Given the following configuration:

.. approved:: ../../examples/Command/report-component-barchart-aggregate
  :language: bash
  :section: 0

When we run PHPBench with the configured report above:


.. approved:: ../../examples/Command/report-component-barchart-aggregate
  :language: bash
  :section: 1

Then it generates the following with the ``console`` renderer:

.. approved:: ../../examples/Command/report-component-barchart-aggregate
  :language: bash
  :section: 2

See Also
--------

- :doc:`../../examples/hashing`: Example barchart comparing hashing
  algorithms.
