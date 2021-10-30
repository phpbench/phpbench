Report Renderers
================

Reports are rendered to an output medium using classes
implementing the ``PhpBench\Report\RendererInterface``.

The configuration for a renderer is known here as an *output*. The user may
define new outputs either in the :doc:`configuration <configuration>` file or
on the CLI. The renderer may also supply default outputs.

.. _renderer_console:

``console``
-----------

Renders directly to the console.

Options:

.. include:: ./report-renderers/options/_console.rst

``delimited``
-------------

The delimited renderer outputs the report as a delimited value list (for
example a tab separated list of values). Such data can be easily imported into
applications such as GNUPlot_.


Options:

.. include:: ./report-renderers/options/_delimited.rst

``html``
-----------

Render the report to a HTML document.

Options:

.. include:: ./report-renderers/options/_html.rst

.. _GNUPlot: http://www.gnuplot.info/

Given the following configuration:

.. approved:: ../examples/Command/report-output-html
  :language: bash
  :section: 0

When we run PHPBench with the configured report above:

.. approved:: ../examples/Command/report-output-html
  :language: bash
  :section: 1

Then it will generate a HTML report to the configured path with the given
title:

.. approved:: ../examples/Command/report-output-html
  :language: bash
  :section: 2
