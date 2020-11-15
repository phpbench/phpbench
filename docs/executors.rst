Executors
=========

Executors are the classes which perform the work and take the measurements.

The default executor is ``remote``.

.. _executor_remote:

``remote``
----------

Executes you benchmark in a separate process for each iteration.

This benchmark records:

- Time in microseconds
- Memory usage

.. _executor_local:

``local``
---------

Executes the benchmark in the same process as PHPBench - only useful if you
have included PHPBench as a dependency in your project and it shares the same
autoloader.

This benchmark records:

- Time in microseconds

.. _executor_debug:

``debug``
---------

The debug executor returns a constant set of results, and is useful for
debugging.
