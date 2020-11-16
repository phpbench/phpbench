Assertions
==========

Assertions can be added to your benchmarks as follows:

.. code-block:: php

    /**
     * @Assert("variant.mode < 100 microseconds +/- 10%")
     */
    public function benchFoobar()
    {
        // ...
    }

Data
----

There are two sources of data:

- ``variant``: Data relating to the executed variant (i.e. the executed benchmark).
- ``baseline``: Data from any referenced *baseline*. If no baseline was given,
  the baseline will be the same as the ``variant``. See
  :doc:`regression-testing` for information on creating baselines.

For each of these the following data is available:

.. csv-table::
    :header: "path", "description"

    "min", "Minimum iteration time"
    "max", "Maximum iteration time"
    "mean", "Mean (average) iteration time"
    "sum", "Sum of all iteration times"
    "stdev", "Standard deviation of the iteration times"
    "rstdev", "Relative standard deviation of the iteration times (a percentage value)"
    "variance", "Variance of the iteration times"
    "mem_real", "Real memory usage (``memory_get_usage(true)``)"
    "mem_final", "Memory usage (``memory_get_usage()``)"
    "mem_peak", "Peak memory usage (``memory_get_peak_usage()``)"

The data can be accessed using dot notation::

    variant.mode < baseline.mode

Comparators
-----------

All assertions involve a type of comparison, the following comparators are
supported:

.. csv-table::
    :header: "comparator", "description"

    "<", "Less than"
    "<=", "Less than or equal"
    "=", "Equal"
    ">", "Greater than"
    ">=", "Greater than or equal"

For example:

::

    10 microseconds < 20 microseconds


Time Units
----------

Values can be expressed in the following ways

.. csv-table::
    :header: "unit", "description"

    "microsecond", "1,000,000th of a second"
    "millisecond", "1,000th of a second"
    "second", "1 second"
    "minute", "60 seconds"
    "hour", "3,600 seconds"
    "day", "86,400 seconds"

For example:

::

    20 seconds < 20 days

Memory Units:
-------------

.. csv-table::
    :header: "unit", "description"

    "byte", "1 byte"
    "kilobyte", "1,000 bytes"
    "megabyte", "1,000,000 bytes"
    "gigabyte", "1,000,000,000 bytes"

For example:

::

    variant.mem_peak < 2 megabytes

Throughput
----------

You cap specify throughput (operations per time unit) by prefixing a time unit
with ``ops/``:

::

    variant.mode > 2 ops/second

Tolerance
---------

In practice two runs will rarely return exactly the same result. To allow a
tolerable variance you can specify a tolerance as follows:

::

    variant.mode <= 10 milliseconds +/- 2 milliseconds

With the above both 11 and 12 milliseconds will be tolerated.

You can also specify a percentage value:

::

    variant.mode <= 10 milliseconds +/- 10%
