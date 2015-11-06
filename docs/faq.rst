FAQ
===

Why do ``setUp` and ``tearDown`` methods not automatically get called?
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
     * @AfterMethods({"setUp"})
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
