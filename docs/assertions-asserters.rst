Asserters
=========

In PHPBench, asserters are classes which perform assertions on the results of
a benchmark variant.

``comparator``
--------------

The comparator asserter simply allows you to assert that a given metric was
less than or greater than a given value, in the given unit of measurement,
with a given tolerance.

It is the default (and currently only) asserter.

Options:

- **comparator**: Comparator to use (``<`` or ``>``), default ``<``.
- **mode**: Either ``throughput`` or ``time``.
- **stat**: Aggregate metric to measure against, e.g. ``mean``, ``mode``,
  ``min``, ``max``, ``stdev``, etc. Default ``mean``.
- **time_unit**: Time unit, e.g. ``milliseconds``, ``seconds``. Default
  ``microseconds``.
- **tolerance**: If the value is less than or greater than the tolerance a
  warning will be issued, but no failure will occur.
- **value**: Assert to this value.

For example:

Assert less than 1234 microseconds:

.. code-block:: php

    /**
     * @Assert(1234)
     */
    public function benchFoobar()
    {
        // ...
    }

Assert a throughput greater than 0.25ops/µs:

.. code-block:: php

    /**
     * @Assert(0.25, comparator=">", "mode": "thoughput")
     */
    public function benchFoobar()
    {
        // ...
    }

Or on the command line:

.. code-block:: bash

    $ phpbench run --assert='value: 0.25, comparator: ">", mode: "throughput"'
