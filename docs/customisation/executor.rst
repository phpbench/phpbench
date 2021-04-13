Executors
=========

An executor is responsible for executing your benchmarks. It accepts an
``ExecutionContext`` and returns ``ExecutionResults``.

PHPBench comes with two executor variations:

- The :ref:`executor_remote` executor executes you benchmarks in a separate
  process using a generated template.
- The :ref:`executor_local` executor executes you benchmarks the same process
  (sharing the runtime environment of PHPBench).

This executor will return a constant configured value for each iteration.

.. codeimport:: ../../examples/Extension/Executor/AcmeExecutor.php
  :language: php

You can register it in your :doc:`extension <extension>` as follows:

.. codeimport:: ../../examples/Extension/AcmeExtension.php
  :language: php
  :sections: all,executor_di
