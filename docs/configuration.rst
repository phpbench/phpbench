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
                "extends": "aggregate"
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

.. _configuration_reports:

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
properties are the options for the report. Each report must specify either
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

Time Unit and Mode
------------------

Specify the *default* :ref:`time unit <time_unit>`. Note that this will be overridden by
individual benchmark/subjects and when the ``time-unit`` option is passed to
the CLI.

.. code-block:: javascript

    {
        "time_unit": "milliseconds"
    }

Similarly the :ref:`mode` can be set using the `output_mode` key:

.. code-block:: javascript

    {
        "output_mode": "throughput"
    }

PHP Binary and INI settings
---------------------------

You can change the PHP binary and INI settings used to execute the benchmarks:

.. code-block:: javascript

    {
        "php_binary": "hhvm",
        "php_config": {
            "memory_limit": "10M"
        }
    }

Prefixing the Benchmarking Process
----------------------------------

You can prefix the benchmarking command line using the ``php_wrapper`` option:

.. code-block:: javascript

    {
        "php_wrapper": "blackfire run -q"
    }

.. note::

    This can also be set using the ``--php-wrapper`` CLI option.

.. _configuration_subject_pattern:

Customizing the subject matching pattern
----------------------------------------

By default PHPBench will identify subject methods when they have a ``bench``
prefix. It is possible to change the regex pattern used to identify subjects
as follows:

.. code-block:: javascript

    {
        "subject_pattern": "^spin_"
    }

The above will allow you to have benchmark class such as:

.. code-block:: php

    <?php

    class Foobar
    {
        public function spin_kde()
        {
            // ...
        }

        public function spin_lcd()
        {
            // ...
        }
    }

.. note::

    You can also explicitly declare that methods are benchmark subjects by
    using the ``@Subject`` annotation.

.. _configuration_disable_php_ini:

Disable the PHP INI file
------------------------

PHP extensions, especially Xdebug, can drastically affect the performance of
your benchmark subjects. You can disable Xdebug and other dynamically loaded
extensions by setting ``php_disable_ini`` to ``true``.

.. note:

    PHPBench currently makes use of the ``json`` extension in remote
    processes, so you are required to explicitly enable it as follows.

.. code-block:: javascript

    {
        "php_disable_ini": true,
        "php_config": {
            "extension": [ "json.so" ]
        }
    }
