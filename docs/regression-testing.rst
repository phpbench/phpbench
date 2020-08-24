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

.. code-block::

    +--------------+------------+-----+-------------------+---------+----------------+----------------+---------+---------------+
    | benchmark    | subject    | set | mem_peak          | best    | mean           | mode           | worst   | rstdev        |
    +--------------+------------+-----+-------------------+---------+----------------+----------------+---------+---------------+
    | HashingBench | benchAlgos | 0   | 1,170,000b +0.00% | 1.004μs | 1.048μs +2.57% | 1.036μs +2.31% | 1.156μs | 3.91% +28.98% |
    | HashingBench | benchAlgos | 1   | 1,170,000b +0.00% | 1.332μs | 1.366μs -0.56% | 1.356μs -0.25% | 1.435μs | 2.19% +8.02%  |
    +--------------+------------+-----+-------------------+---------+----------------+----------------+---------+---------------+

The ``mean``, ``mode`` and ``rstdev`` columns show the percentage difference
from the previous run.
