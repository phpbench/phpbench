FAQ
===

PHPBench is slow on Windows
---------------------------

Process spawning on Windows is more expensive than on Linux/MacOS, PHPBench spawns
many processes. Actual benchmarking time however is not affected.

PHPBench's output looks corrupted on Windows
--------------------------------------------

PHPBench makes use of ansi escape sequences in most of its progress loggers.
The default Windows console does not support these sequences, so the output
can look corrupted.

You can mitigate this by using the `travis` logger, which does not issue any
of these escape sequences.

Why do `setUp` and `tearDown` methods not automatically get called?
----------------------------------------------------------------------

PHPBench supports the annotations ``BeforeMethods`` and ``AfterMethods`` which
can be placed at the class level and/or the method level. These methods are
plural. If we were to automatically add ``setUp`` to the chain then the
annotation would read one thing, but the benchmark would do another (i.e.
execute the method indicated by the annotation and the "magic" setUp method).

If you want to support ``setUp`` and ``tearDown`` you can create a simple base
class such as:

.. code-block:: php

    /**
     * @BeforeMethods({"setUp"})
     * @AfterMethods({"tearDown"})
     */
    abstract class BenchmarkCase
    {
        public function setUp()
        {
        }

        public function tearDown()
        {
        }
    }
