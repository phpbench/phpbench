Command
=======

Commands let you add custom CLI commands to PHPBench. For example we can add a
command which prints cats to the console output.

Create a new `Symfony Command <command>`_ similar to the following:

.. codeimport:: ../../../examples/Extension/Command/CatsCommand.php
  :language: php
  :section: command

Register it with the :doc:`DI extension <extensions>`:

.. codeimport:: ../../../examples/Extension/AcmeExtension.php
  :language: php
  :section: command_di

You can then run your command:

.. code-block:: bash

    $ phpbench cats
    🐈🐈🐈🐈🐈🐈🐈
