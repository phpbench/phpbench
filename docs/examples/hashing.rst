Hashing Benchmark
=================

This hashing benchmark accepts two parameter sources: one for algorithms and the other
for the size of the string to hash.

Parameters from different providers are combined into a cartesian product,
meaning that each algorithm will be combined with each size.

.. codeimport:: ../../examples/Benchmark/Hashing/HashingBench.php
  :language: php
  :sections: all

You can configure a bar chart report grouping both by size and algorithm:

.. code-block:: json

    {
        "report.generators": {
            "hashing": {
                "generator": "component",
                "filter": "benchmark_name = 'HashingBench'",
                "components": [
                    {
                        "component": "bar_chart_aggregate",
                        "x_partition": "variant_params['algo']",
                        "bar_partition": "variant_params['size']",
                        "y_expr": "mode(partition['result_time_avg']) as time",
                        "y_axes_label": "yValue as time"
                    }
                ]
            }
        }
    }

.. figure:: ../images/example_hashing.png
   :alt: Hashing Report

   Console Output
