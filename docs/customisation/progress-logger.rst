Progress Loggers
================

Progress loggers show the real-time progress of the test suite.

The progress logger has many methods which are called during the lifetime of
the PHPBench run.

Create a new progress-logger class similar to the following:

.. codeimport:: ../../examples/Extension/ProgressLogger/CatLogger.php
  :language: php

And register with your DI container:

.. codeimport:: ../../examples/Extension/AcmeExtension.php
  :language: php
  :sections: all,progress_logger_di

Run it with:

.. code-block:: bash

  $ phpbench run --progress=cats
