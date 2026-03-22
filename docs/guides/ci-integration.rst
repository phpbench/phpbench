.. _ci_integration:

Continuous Integration
======================

Automatically detecting performance regressions is a holy grail for
benchmarking enthusiasts. Unfortunately your CI servers are almost certainly
unstable environments which makes them unsuitable for picking up subtle changes
in performance. You can, however, reliably detect major performance regressions
and, if monitoring performance over time, identfy performance trends.

The following methods are unofficial recommendations and, where applicable, you
use them at your own risk.

Github Actions Benchmarks
-------------------------

The `github-action-benchmark`_ is a github action that uses github pages to
store historical benchmarking results and builds a static page containing all the results.

In order to use it you need to configure a report in PHPBench, introduce the
followng :ref:`report confguration`<configuration_report_generators>` in
``phpbench.json``:

.. literalinclude:: ../../examples/Benchmark/Hashing/github_action_benchmark.json
  :language: json

You can now generate the JSON document required by the github action:

.. code-block:: bash

    $ phpbench run examples/HashBench.php --report=github-action-benchmark --output=json > output.json

Now introduce the github workflow:

.. literalinclude:: ../../.github/workflows/benchmark.yml
  :language: yaml

In the above example we publish the results to the `benchmarks`. The PHPBench report can be seen here: https://phpbench.github.io/phpbench/benchmarks/

.. note::

   The above workflow only runs on the **main branch** and does not provide any feedback on pull requests. The github actin can also automatically comment on pull requests when regressions are detected. See the action's README file for more information.

.. _github-action-benchmark: https://github.com/benchmark-action/github-action-benchmark
