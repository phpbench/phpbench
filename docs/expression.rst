Expression Language
===================

PHPBench has a general purpose expression language which is used when writing assertions.

.. contents::
    :depth: 3
    :local:

.. _expr_comparators:

Comparators
-----------

.. csv-table::
    :header: "comparator", "description"

    "<", "Less than"
    "<=", "Less than or equal"
    "=", "Equal"
    ">", "Greater than"
    ">=", "Greater than or equal"

Examples:

.. literalinclude:: ../examples/Expression/comparison_1

.. _expr_logical_operators:

Logical Operators
-----------------

.. csv-table::
    :header: "comparator", "description"

    "and", "Logical and"
    "or", "Logical or"

Examples:

.. literalinclude:: ../examples/Expression/logical_operators_1

.. _expr_string:

String Operators
-----------------

.. csv-table::
    :header: "comparator", "description"

    "``~``", "Concatenate"

Examples:

.. literalinclude:: ../examples/Expression/string_operators_1

.. _expr_arithmetic:

Arithmetic
----------

.. csv-table::
    :header: "comparator", "description"

    "``+``", "Addition"
    "``-``", "Subtraction"
    "``/``", "Divide"
    "``*``", "Multiply"

Examples:

.. literalinclude:: ../examples/Expression/arithmetic_1

.. _expr_time_units:

Time Units
----------

The base unit for time in PHPBench is microseconds. By specifying a timeunit
you can represent other units - internally the unit will be converted to
microseconds:

.. csv-table::
    :header: "unit", "description"

    "microsecond", "1,000,000th of a second"
    "millisecond", "1,000th of a second"
    "second", "1 second"
    "minute", "60 seconds"
    "hour", "3,600 seconds"
    "day", "86,400 seconds"

Examples:

.. literalinclude:: ../examples/Expression/time_unit_1

.. _expr_memory_units:

Memory Units:
-------------

.. csv-table::
    :header: "unit", "description"

    "byte", "1 byte"
    "kilobyte", "1,000 bytes"
    "megabyte", "1,000,000 bytes"
    "gigabyte", "1,000,000,000 bytes"

For example:

.. literalinclude:: ../examples/Expression/memory_unit_1

.. _expr_display_as:

Display As
----------

Parameterized values (e.g. `variant.time.avg`) are always provided the base
unit. You can use ``as`` to display these values in a specified unit:

.. literalinclude:: ../examples/Expression/time_unit_2

This will force the unit conversion to happen only when displaying the
evaluated expression.

.. _expr_tolerance:

Tolerance
---------

In practice two runs will rarely return exactly the same result. To allow a
tolerable variance you can specify a tolerance as follows:

.. literalinclude:: ../examples/Expression/tolerance_1

With the above values within 8 to 12 milliseconds will be tolerated.

You can also specify a percentage value:

.. literalinclude:: ../examples/Expression/tolerance_2

.. _expr_functions:

Functions
---------

.. _expr_func_max:

max
~~~

Return the max value in a set of values:

.. literalinclude:: ../examples/Expression/func_max

.. _expr_func_mean:

mean
~~~~

Return the mean (i.e. average) value in a set of values:

.. literalinclude:: ../examples/Expression/func_mean

.. _expr_func_min:

min
~~~

Return the min value in a set of values:

.. literalinclude:: ../examples/Expression/func_min

.. _expr_func_mode:

mode
~~~~

Return the `KDE mode`_ value in a set of values:

.. literalinclude:: ../examples/Expression/func_mode

In PHPBench the mode is generally the best predictor.

.. _expr_percent_diff:

percent_diff
~~~~~~~~~~~~

Return the percentage difference between two values

.. literalinclude:: ../examples/Expression/func_percent_diff

.. _expr_stddev:

stdev
~~~~~

Return the `standard deviation`_ for a set of values:

.. literalinclude:: ../examples/Expression/func_stdev

.. _expr_rstddev:

rstdev
~~~~~~

Return the `relative standard deviation`_ for a set of values:

.. literalinclude:: ../examples/Expression/func_rstdev

.. _expr_variance:

variance
~~~~~~~~

Return the `variance`_ for a set of values:

.. literalinclude:: ../examples/Expression/func_variance

format
~~~~~~

Format values as a string - uses `sprintf`_:

.. literalinclude:: ../examples/Expression/func_format

.. _KDE mode: https://en.wikipedia.org/wiki/Kernel_density_estimation
.. _standard deviation: https://en.wikipedia.org/wiki/Standard_deviation
.. _variance: https://en.wikipedia.org/wiki/Variance
.. _relative standard deviation: https://en.wikipedia.org/wiki/Coefficient_of_variation
.. _sprintf: https://www.php.net/manual/en/function.sprintf.php

