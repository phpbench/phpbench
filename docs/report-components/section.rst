Section
=======

The ``section`` component is identical to the
:doc:`Component Generator <../report-generators/component>`.

Use it to nest other components within a report and to partition the data
frame.

Options
-------

.. include:: options/_section.rst

Example
-------

Given the following configuration:

.. approved:: ../../examples/Command/report-component-section
  :language: bash
  :section: 0

When we run PHPBench with the configured report above:


.. approved:: ../../examples/Command/report-component-section
  :language: bash
  :section: 1

Then it generates the following with the ``console`` renderer:

.. approved:: ../../examples/Command/report-component-section
  :language: bash
  :section: 2
