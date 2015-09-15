Configuration
=============

Unless overridden with the ``--config`` option, PHPBench will first try to
load its configuration from ``phpbench.json`` and then try
``phpbench.json.dist``.

A typical configuration file might look as follows:

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

See the :doc:`reporting` chapter for more information.
