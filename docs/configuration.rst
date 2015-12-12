Configuration
=============

Unless overridden with the ``--config`` option, PHPBench will try to load its
configuration from the current working directory. It will check for the
existence each of the files ``phpbench.json`` and ``phpbench.json.dist`` in
that order and use one if it exists.

.. code-block:: javascript

    {
        "bootstrap": "vendor/autoload.php",
        "path": "path/to/benchmarks",
        "outputs": {
             "my_output": {
                 "extends": "html",
                 "file": "my_report.html",
                 "title": "Hello World"
             }
        },
        "reports": {
            "my_report": {
                "extends": "aggregate",
                "exclude": ["benchmark"]
            }
        }
    }

.. note::

    Typically you should use ``phpbench.json.dist`` in your projects. This
    allows the end-user of your library to override your configuration by creating
    ``phpbench.json``.

.. _configuration_bootstrap:

Bootstrap
---------

You can include a single file, the bootstrap file, before the benchmarks are
executed. Typically this will be the class autoloader (e.g.
``vendor/autoload.php``).

It is specified with the ``bootstrap`` key:

.. code-block:: javascript

    {
        "bootstrap": "vendor/autoload.php",
    }

.. note::

    You can override (or initially set) the bootstrap using the
    ``--bootstrap`` CLI option.

Path
----

Specify the default path to the benchmarks:

.. code-block:: javascript

    {
        "path": "tests/benchmarks"
    }

Progress Logger
---------------

Specify which progress logger to use:

.. code-block:: javascript

    {
        "progress": "dots"
    }

.. _configuration_retry_threshold:

Retry Threshold
---------------

Set the :ref:`retry_threshold`:

.. code-block:: javascript

    {
        "retry_threshold": 5
    }

Reports
-------

List of report definitions:

.. code-block:: javascript

    {
        "reports": {
            "my_report": {
                "extends": "aggregate",
                "exclude": ["benchmark"]
            }
        }
    }

The key is the name of the report that you are defining, and the object
properties are the options for the report. Eeach report must specify either
the ``generator`` or ``extends`` key, specifying the :doc:`generator
<report-generators>` or report to extend respectively.

See the :doc:`report-generators` chapter for more information on report
configuration.

Outputs
-------

Custom output definitions:

.. code-block:: javascript

        "outputs": {
             "my_output": {
                 "extends": "html",
                 "file": "my_report.html",
                 "title": "Hello World"
             }
        }

Note that:

- The key of each definition is the output name.
- As with reports, each definition *MUST* include either the ``renderer`` or
  ``extends`` key.
- All other options are passed to the renderer as options.

See the :doc:`report-renderers` chapter for more information.

Time Unit
---------

Specify the *default* :ref:`time unit <time_unit>`. Note that this will be overridden by
individual benchmark/subjects and when the ``time-unit`` option is passed to
the CLI.

.. code-block:: javascript

    {
        "time_unit": "milliseconds"
    }
