Config Linters
==============

.. figure:: ../images/example_config_linter.png
   :alt: Configuration Linters

   HTML Output

One approach for comparing classes which implement the same interface is to
create an abstract `BenchCase` class which sets up the fixtures and delegates
to a child class to create the concerete instance of the class we want to
test:

.. codeimport:: ../../examples/Benchmark/ConfigLinters/LinterBenchCase.php
  :language: php
  :sections: all

The concrete benchmarks will look like:

.. codeimport:: ../../examples/Benchmark/ConfigLinters/SeldLinterBench.php
  :language: php
  :sections: all

The report can use the `@Groups` defined in the abstract class to compare
these benchmarks:

.. literalinclude:: ../../examples/Benchmark/ConfigLinters/config_linters_report.json
  :language: json
