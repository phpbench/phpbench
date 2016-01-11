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

Class: ``PhpBench\Report\Renderer\ConsoleRenderer``.

Options:

- **table_style**: *(string)* Table style to use, one of: ``default``,
  ``compact``, ``borderless`` or ``symfony-style-guide``.

Default outputs:

- ``console``: Renderers the report directly to the console. This is the
  **default** output method.

.. _renderer_xslt:

``xslt``
--------

The XSLT renderer the path to an XSLT template which will be used to transform
the report XML document into an output *file*.

Class: ``PhpBench\Report\Renderer\XsltRenderer``.

Options:

- **title**: *(string)*: Title to use for the document (where applicable).
- **template**: *(string)*: Path to the XSL template.
- **file**: *(string)*: Path to the output file (existing files will be
  overwritten). You can use the ``%report_name%`` token, it will be replaced
  with the name of the report.

Default outputs:

- ``html``: Render the report as a single HTML page.
- ``markdown``: Render the report as a `GitHub Flavored Markdown`_ document.

``delimited``
-------------

The delimited renderer outputs the report as a delimited value list (for
example a tab separated list of values). Such data can be easily imported into
applications such as GNUPlot_.

Class: ``PhpBench\Report\Renderer\DelimitedRenderer``.

Options:

- **delimiter**: *(string)*: Path to the output file (existing files will be
  overwritten).
- **header**: *(boolean)*: If a header should be included in the output.

Default outputs:

- ``delimiter``: The delimiter to use.

``debug``
---------

Output the raw XML of the report document. Useful for debugging.

Options:

**none**

Default outputs:

- ``debug``: Outputs the report document's XML.

.. _GitHub Flavored Markdown: https://help.github.com/articles/github-flavored-markdown: 
.. _GNUPlot: http://www.gnuplot.info/
