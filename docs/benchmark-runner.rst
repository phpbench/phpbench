Benchmark Runner
================

The benchmark runner is a command line application which executes the
benchmarks and generates reports from the results.

Running Benchmarks
------------------

To run all benchmarks in a specific directory:

.. code-block:: bash

    $ phpbench run /path/to

To run a single benchmark class, specify a specific file:

.. code-block:: bash

    $ phpbench run /path/to/HashBench.php

To run a single method of a single benchmark class, add the ``--subject``
option:

.. code-block:: bash

    $ phpbench run /path/to/HashBench.php --subject=benchMd5

Groups can be specified using the ``--group`` option:

.. code-block:: bash

    $ phpbench run /path/to/HashBench.php --group=hash

.. note::

    Both ``--subject`` and ``--group`` options may be specified multiple
    times.

Generating Reports
------------------

By default PHPBench will run the benchmarks and tell you that the benchmarks
have been executed successfully. In order to see some useful information you
can specify that a report be generated.

By default there are two reports ``default`` and ``aggregate``, and they can
be specified directly using the ``--report`` option:

.. code-block:: bash

    $ phpbench run /path/to/HashBench.php --report=default

If you want to experiment with a new report configuration, you can also pass a
JSON encoded string with the generator options instead of the report name:

.. code-block:: bash

    $ phpbench run /path/to/HashBench.php --report='{"extends": "default", "exclude": ["benchmark", "subject"]}'

See the :doc:`reporting` chapter for more information on reports.

The ``--report`` option can be specified multiple times.

Deffering Report Generation
---------------------------

You can dump the benchmarking results as an XML file and generate reports
separately.

Dump the benchmark results using the ``--dump-file`` option

.. code-block:: bash

    $ phpbench run /path/to/HashBench.php --dump-file=report.xml

You can then generate reports using the ``report`` command:

.. code-block:: bash

    $ phpbench report report.xml --report=default

Progress Reporters
------------------

By default PHPBench issues a single ``.`` for each benchmark subject executed.
This is the ``dots`` progress reporter. Different progress reporters can be
specified using the ``--progress`` option:

.. code-block:: bash

    $ phpbench run /path/to/HashBench.php --progress=classdots

Configuration File
------------------

Unless a configuration file is specified using the ``--config`` option,
PHPBench will look in the current working directory for a file named, firstly
``phpbench.json.dist`` and then ``phpbench.json``.

See the :doc:`configuration` chapter for more information.
