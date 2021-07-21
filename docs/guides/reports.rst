Reports
=======

PHPBench includes a reporting framework. :doc:`Report
generators <../report-generators>` provide report data which can subsequently be
rendered by :doc:`report renderer
<../report-renderers>` (e.g. ``console`` or ``html``).

This guide will deal with generating reports and assume that the ``console``
renderer is used.

Generating Reports
------------------

To report after a benchmarking run:

.. approved:: ../../examples/Command/run-reports-aggregate
  :language: bash
  :section: 1

Multiple reports can be specified:

.. approved:: ../../examples/Command/run-reports-aggregate-and-env
  :language: bash
  :section: 1

You can also tag runs and generate reports afterwards:

.. approved:: ../../examples/Command/report-ref-latest-and-aggregate
  :language: bash
  :section: 1

For more information on storage see :doc:`storage <storage>`.

Configuring Reports
-------------------

All reports can be configured either in the :ref:`report configuration
<configuration_report_generators>` or directly on the command line using a simplified
JSON encoded string instead of the report name:

To configure a report in ``phpbench.json``:

.. approved:: ../../examples/Command/run-configuring-reports-phpbenchjson
  :language: javascript
  :section: 0

Then run it with:

.. approved:: ../../examples/Command/run-configuring-reports-phpbenchjson
  :language: bash
  :section: 1

You can also configure reports directly from the command line using simplified
JSON:

.. approved:: ../../examples/Command/run-configuring-reports
  :language: bash
  :section: 1

In each case it is required to specify the ``generator`` key which corresponds
to the registered name of the :doc:`report generator <../report-generators>`.

You may also **extend** an existing report configuration:

.. approved:: ../../examples/Command/run-configuring-reports-extend
  :language: bash
  :section: 1

This will merge the given keys onto the configuration for the `aggregate report`_.

Default Reports
---------------

.. _report_aggregate:

``aggregate``
~~~~~~~~~~~~~

Shows aggregate details of each set of iterations.

Run:

.. approved:: ../../examples/Command/run-reports-aggregate
  :language: bash
  :section: 1

And get something like:

.. approved:: ../../examples/Command/run-reports-aggregate
  :language: bash
  :section: 2

It is uses the ``table`` generator, see :ref:`generator_expression` for more information.

.. _report_benchmark:

``benchmark``
~~~~~~~~~~~~~

Sumarize each benchmark:


Run:

.. approved:: ../../examples/Command/run-reports-benchmark
  :language: bash
  :section: 1

And get something like:

.. approved:: ../../examples/Command/run-reports-benchmark
  :language: bash
  :section: 2


.. _report_default:

``default``
~~~~~~~~~~~

The default report presents the result of *each iteration*:

Run:

.. approved:: ../../examples/Command/run-reports-default
  :language: javascript
  :section: 1

And get something like:

.. approved:: ../../examples/Command/run-reports-default
  :language: javascript
  :section: 2

It is uses the ``table`` generator, see :ref:`generator_expression` for more information.

.. _report_env:

``env``
~~~~~~~

This report shows information about the environment that the benchmarks were
executed in.

Run:

.. approved:: ../../examples/Command/run-reports-env
  :language: bash
  :section: 1

And get something like:

.. approved:: ../../examples/Command/run-reports-env
  :language: bash
  :section: 2

Generator: :ref:`generator_env`.

Columns:

- **provider**: Name of the environment provider (see
  ``PhpBench\Environment\Provider`` in the code for more information).
- **key**: Information key.
- **value**: Information value.

See the :doc:`../environment` reference for more information.

.. note::

    The information available will differ depending on platform. For example,
    ``unix-sysload`` is only available on UNIX platforms, where
    as the VCS field will appear only when a *supported* VCS system is being
    used.

.. _aggregate report: https://github.com/phpbench/phpbench/blob/master/lib/Extension/config/report/generators.php

.. _report_overview:

``overview``
~~~~~~~~~~~~~

Shows overview of a single or mutiple runs:

Run:

.. approved:: ../../examples/Command/run-reports-overview
  :language: bash
  :section: 1

And get something like:

.. approved:: ../../examples/Command/run-reports-overview
  :language: bash
  :section: 2
