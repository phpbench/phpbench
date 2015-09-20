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
        "progress_logger_name": "dots"
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
<report_generators>` or report to extend respectively.

See the :doc:`report_generators` chapter for more information on report
configuration.
