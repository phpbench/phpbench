Commands
========

Commands let you add custom CLI commands to PHPBench. For example we can add a
command which prints cats to the console output.

Create a new `Symfony Command`_ similar to the following:

.. codeimport:: ../../examples/Extension/Command/CatsCommand.php
  :language: php

Register it with the :doc:`DI extension <extension>`:

.. codeimport:: ../../examples/Extension/AcmeExtension.php
  :language: php
  :sections: all,command_di

You can then run your command:

.. code-block:: bash

    $ phpbench cats
    🐈🐈🐈🐈🐈🐈🐈

.. _Symfony Command: https://symfony.com/doc/current/console.html
