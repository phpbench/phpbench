Progress Logger
===============

Progress loggers show the real-time progress of the test suite.

Create a new progress-logger class similar to the following:

.. codeimport:: ../../../examples/Extension/ProgressLogger/CatLogger.php
  :language: php

And register with your DI container:

.. codeimport:: ../../../examples/Extension/AcmeExtension.php
  :language: php
  :section: progress_logger_di

