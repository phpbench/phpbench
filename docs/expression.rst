Expression Language
===================

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

.. literalinclude:: ../examples/Expression/comparison_1

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
