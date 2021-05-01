Writing Benchmarks
==================

Benchmark classes have the following characteristics:

- The class and filename must be the same.
- Class methods that start with ``bench`` will be executed by the benchrunner
  and timed.

PHPBench does not require that the benchmark class be aware of PHPBench
library - it does not need to extend a parent class or implement an interface.

The following is a simple benchmark class:

.. code-block:: php

    // HashBench.php
    class HashBench
    {
        public function benchMd5()
        {
            hash('md5', 'Hello World!');
        }

        public function benchSha1()
        {
            hash('sha1', 'Hello World!');
        }
    }

And it can be executed as follows:

.. code-block:: bash

    $ phpbench run examples/HashBench.php --progress=dots
    Running benchmarks.

    ... 

    3 subjects, 30 iterations, 30000 revs, 0 rejects
    ⅀T: 30543μs μSD/r 0.05μs μRSD/r: 4.83%
    min mean max: 0.78 1.02 1.47 (μs/r)

.. note::

    The above command does not generate a report, add ``--report=default`` to
    view something useful.

PHPBench reads docblock annotations in the benchmark class. Annotations can be
placed in the class docblock, or on individual methods docblocks.

.. note::

    Instead of prefixing a method with ``bench`` you can use the
    ``@Subject`` annotation or specify a :ref:`custom pattern <configuration_runner_subject_pattern>`.

Benchmark Configuration
-----------------------

See the :doc:`Annotations and Attributes<../annotributes>` reference to see how
you can configure your benchmarks.
