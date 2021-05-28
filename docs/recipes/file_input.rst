File Input
==========

If your benchmark requires input from a file, or from files.

.. codeimport:: ../../examples/Benchmark/Pattern/FileInputBench.php
  :language: php
  :sections: all

Note that we ``yield`` the filename as the key, which is then used as
the parameter set name:

.. code-block:: text

    \PhpBench\Examples\Benchmark\Pattern\FileInputBench

        benchFind # example1....................I9 ✔ Mo0.205μs (±44.54%)
        benchFind # example2....................I9 ✔ Mo0.337μs (±46.15%)
