Regression Testing
==================

Sometimes you need to ensure that modifications to existing code do not cause
performance regressions.

PHPBench allows you to store results and use them as a baseline for subsequent
runs.

Run PHPBench on the original code and store the result by specifying a
``--tag``:

.. code-block:: bash

    phpbench run tests/Benchmark/MyBenchmark.php --tag=original

Then switch to the new version of your code and run PHPBench again:

.. code-block:: bash

    phpbench run tests/Benchmark/MyBenchmark.php --report=aggregate_baseline --uuid=tag:original

- We use ``--uuid`` to reference the previous, tagged, run, which will merge
  the previous benchmarks into the results.
- We use the ``aggregate_baseline`` report which is pre-configured to show
  differences relative to previous benchmarks in the suite.

The final report should look something like:

.. image:: images/baseline.png

The ``mean``, ``mode`` and ``rstdev`` columns show the percentage difference
from the previous run.
