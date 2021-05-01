Assertions
==========

Assertions are executed against each variant of the subject and can be added
to your benchmarks as follows:

.. codeimport:: ../../examples/Assertion/ExampleAssertionsBench.php
  :language: php
  :sections: all,bench_time

Above we assert that the KDE mode of the average iterations times is less than
200 milliseconds give or take a tolerance of 10%.

.. codeimport:: ../../examples/Assertion/ExampleAssertionsBench.php
  :language: php
  :sections: all,bench_time_baseline

Above we compare the current variant with a :ref:`referenced<ref>` variant.

Data
----

PHPBench provides the aggregated iteration results for the current `variant`
and, if a :ref:`reference <ref>` is specified, the `baseline`.

.. codeimport:: ../../examples/Assertion/ExampleAssertionsBench.php
  :language: php
  :sections: data_access

Each metric (e.g. `variant.time.net`) is provided as a list of numbers
corresponding to each iteration. You will need to use an aggregation function
(e.g. :ref:`expr_func_mode` or :ref:`expr_func_mean`) to get a comparable
value.

Assertion Language
------------------

Assertions are created using the :doc:`PHPBench Expression
Language<../expression>`. Each assertion must be a comparison.
