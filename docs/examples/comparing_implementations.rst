Comparing Implementations
=========================

One approach for comparing classes which implement the same interface is to
create an abstract `BenchCase` class which sets up the fixtures and delegates
to a child class to create the concerete instance of the class we want to
test:

.. codeimport:: ../../examples/Benchmark/Implementations/LinterBenchCase.php
  :language: php
  :sections: all

The concrete benchmarks will look like:

.. codeimport:: ../../examples/Benchmark/Implementations/SeldLinterBench.php
  :language: php
  :sections: all

The report can use the `@Groups` defined in the abstract class to compare
these benchmarks:

.. literalinclude:: ../../examples/Benchmark/Implementations/implementations_report.json
  :language: json



