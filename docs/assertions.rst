Assertions
==========

Assertions can be added to your benchmarks as follows:

.. code-block:: php

    /**
     * @Assert("mode(variant.time.avg) as ms < 10 ms")
     */
    public function benchFoobar()
    {
        // ...
    }

This will assert that the subject should complete in under 10 milliseconds.

You can also compare aginst baselines (see :doc:`regression-testing`):

.. code-block:: php

    /**
     * @Assert("mode(variant.time.avg) as ms <= mode(baseline.time.avg) as ms +/- 10%")
     */
    public function benchFoobar()
    {
        // ...
    }

Data
----

You can access the results for both the variant you are testing and any
baseline you are comparing against.

You can access any data available in the variant result. Typically you will be
most concerned with time and memory results:

.. code-block:: php

    [
        'variant' => [
            'time' => [
                'net' => [ 10, 10, 10, ... ], // total time for each iteration
                'avg' => [ 2, 2, 2, ... ],    // average time for each revolution
            ],
            'mem' => [
                'peak' => [ 10, 10, 10, ... ],
                'real' => [ 2, 2, 2, ... ],
                'final' => [ 2, 2, 2, ... ],
            ],
        ],
        'baseline' => [
            // ...
        ]
    ]


Time values are in microseconds if no unit is specified, and memory values are
bytes.

You access the values with dot notation, for example to access the list of
"final" memory samples: ``variant.mem.final``

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

In practice two runs will rarely return exactly the same result. To allow a
tolerable variance you can specify a tolerance as follows:

::

    variant.mode <= 10 milliseconds +/- 2 milliseconds

With the above both 11 and 12 milliseconds will be tolerated.

You can also specify a percentage value:

::

    variant.mode <= 10 milliseconds +/- 10%

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

You convert any (microsecond) value into operations per-second:

::

    mode(variant.time.avg) ops/second > 2

Functions
---------

Mode
~~~~

Shows the `KDE mode`_ for a set of values:

::

    mode(variant.time.avg)

The mode is typically more accurate predictor of the true value as it is less
susceptible to distortion by outlying values.

Mean
~~~~

Shows the mean (average) for a set of values:

::

    mean([4, 2, 4])

Tranaslates to `(4 + 2 + 4) / 3`.

.. _KDE mode: https://en.wikipedia.org/wiki/Kernel_density_estimation
