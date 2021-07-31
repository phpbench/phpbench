Extensions
==========

PHPBench allows you to create your own extensions, allowing you to register new:

- :doc:`Benchmark Executors <executor>`
- :doc:`Progress Loggers <progress-logger>`
- :doc:`Commands <command>`
- :doc:`Reports <report>`

Create a new extension package
------------------------------

.. note:: 

    This is optional if you do not wish to distribute your extension
    you can skip this step.

Create a new project, for example:

.. code-block:: bash

    $ composer create-project my-phpbench-extension

Include PHPBench as a dev-depenency:

.. code-block:: bash

    $ composer require phpbench/phpbench --dev

Create the Dependency Injection Extension
-----------------------------------------

First, create a dependency injection container extension:

.. codeimport:: ../../examples/Extension/AcmeExtension.php
  :language: php
  :sections: all

Then it can be registered in ``phpbench.json``:

.. code-block:: javascript

    {
        "$schema":"./vendor/phpbench/phpbench/phpbench.schema.json",
        "core.extensions": [
            "PhpBench\\Examples\\Extension\\AcmeExtension"
        ]
    }

Registering and Retrieving Services
-----------------------------------

You can register new services which will be integrated with PHPBench via
_tags_. 

For example, to register a new :doc:`Command <command>`: with a configuration
parameter:

.. codeimport:: ../../examples/Extension/AcmeExtension.php
  :language: php
  :sections: all,command_di

Note that:

- The container is a PSR-11_ container. You can get any registered service
  with ``$container->get(<< service ID here >>)``.
- The parameter name is prefixed with the name of the extension (``acme.``)
  This will help prevent configuration conflicts.
- A "tag" is used to integrate the new command with PHPBench.

You can activate and use your extension as follows ``phpbench.json``:

.. code-block:: javascript

    {
        "$schema":"./vendor/phpbench/phpbench/phpbench.schema.json",
        "extensions": [
            "PhpBench\Examples\Extension\AcmeExtension"
        ],
        "acme.number_of_cats": 8
    }

.. _PSR-11: https://www.php-fig.org/psr/psr-11/
