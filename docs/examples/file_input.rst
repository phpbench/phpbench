File Input
==========

If your benchmark subject operates on file contents, or can otherwise be
parameterized from the contents of files.

Given you have a directory ``file-input`` containing a list of files, and each
of these files represents a benchmarking scenario:

.. codeimport:: ../../examples/Benchmark/FileInput/FileInputBench.php
  :language: php
  :sections: all

Note that we ``yield`` the filename as the key, which is then used as
the parameter set name:

.. code-block:: text

    \PhpBench\Examples\Benchmark\Pattern\FileInput\FileInputBench

        benchFind # example1....................I9 ✔ Mo0.205μs (±44.54%)
        benchFind # example2....................I9 ✔ Mo0.337μs (±46.15%)
