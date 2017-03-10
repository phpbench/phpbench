FAQ
===

Why does PHPBench slow on Windows?
----------------------------------

Process spawning on Windows is more expensive than on Linux, PHPBench spawns
many processes. Actual benchmarking time however is not affected.

Why does PHPBench look terrible on Windows?
-------------------------------------------

PHPBench makes use of ansi escape sequences in most of its progress loggers.
The default Windows console does not support these sequences, so the output
can look very bad.

You can mitigate this by using the `travis` logger, which does not issue any
of these escape sequences.

You may also consider using `Cgywin`, `emuCon` or `ansiCon` programs to
enhance your console. You may also switch to Linux.

Why do `setUp` and `tearDown` methods not automatically get called?
----------------------------------------------------------------------

PHPBench supports the annotations ``BeforeMethods`` and ``AfterMethods`` which
can be placed at the class level and/or the method level. These methods are
plural. If we were to automatically add ``setUp`` to the chain then the
annotation would read read one thing, but the benchmark would do another (i.e.
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
