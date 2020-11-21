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
    ``@Subject`` annotation or specify a :ref:`custom pattern <configuration_subject_pattern>`.

.. _revolutions:

Revolutions
-----------

When testing units of code where microsecond accuracy is important, it is
necessary to increase the number of *revolutions* performed by the
benchmark runner. The term "revolutions" (invented here) refers to the number
of times the benchmark is executed consecutively within a single time
measurement.

We can arrive at a more accurate measurement by determining the mean time
from multiple revolutions (i.e. ``time / revolutions``) than we could with a
single revolution. In other words, more revolutions means more precision.

Revolutions can be specified using the ``@Revs`` annotation:

.. code-block:: php

    /**
     * @Revs(1000)
     */
    class HashBench
    {
        // ...
    }

You may also specify an array:

.. code-block:: php

    /**
     * @Revs({1, 8, 64, 4096})
     */
    class HashBench
    {
        // ...
    }

Revolutions can also be overridden from the :ref:`command line
<overriding_iterations_and_revolutions>`.

.. _iterations:

Iterations
----------

Iterations specify how many samples should be taken - i.e. how many times we
run the :ref:`revolutions <revolutions>` and capture time and memory information (for example). 

By looking at the separate time measurement of each iteration we can determine
how *stable* the readings are. The less the measurements differ from each
other, the more stable the benchmark.

.. note::

    In a *perfect* environment the readings would all be *exactly* the same -
    but such an environment is unlikely to exist 

Iterations can be specified using the ``@Iterations`` annotation:

.. code-block:: php

    /**
     * @Iterations(5)
     */
    class HashBench
    {
        // ...
    }

As with :ref:`revolutions <revolutions>`, you may also specify an array.

Iterations can also be overridden from the :ref:`command line
<overriding_iterations_and_revolutions>`.

You can instruct PHPBench to continuously run the iterations until the
deviation of each iteration fits within a given margin of error by using the
``--retry-threshold``. See :ref:`retry_threshold` for more information.

Benchmark Hooks
---------------

Method hooks
~~~~~~~~~~~~

Any number of methods can be executed both before and after each benchmark
**subject** using the ``@BeforeMethods`` and
``@AfterMethods`` annotations. Before methods are useful for bootstrapping
your environment:

.. code-block:: php

    /**
     * @BeforeMethods({"init"})
     */
    class HashBench
    {
        private $hasher;

        public function init()
        {
            $this->hasher = new Hasher();
        }

        public function benchMd5()
        {
            $this->hasher->md5('Hello World!');
        }
    }

Multiple before and after methods can be specified.

.. note::

    If before and after methods are used when the ``@ParamProviders``
    annotations are used, then they will also be passed the parameters.

Class Hooks
~~~~~~~~~~~

Sometimes you will want to perform actions which establish an *external*
state. For example, creating or populating a database, creating files, etc.

This can be achieved by creating **static** methods within your benchmark
class and adding the ``@BeforeClassMethods`` and ``@AfterClassMethods``:

These methods will be executed by the runner once per benchmark class.

.. code-block:: php

    /**
     * @BeforeClassMethods({"initDatabase"})
     */
    class DatabaseBench
    {
        public static function initDatabase()
        {
            // init database here.
        }

        // ...
    }

.. note::

    These methods are static and are executed in a process that is separate
    from that from which your iterations will be executed. Therefore **state
    will not be carried over to your iterations!**.

.. _parameters:

Parameterized Benchmarks
------------------------

Parameter sets can be provided to benchmark subjects:

.. code-block:: php

    class HashBench
    {
        public function provideStrings()
        {
            yield 'hello' => [ 'string' => 'Hello World!' ];
            yield 'goodbye' => [ 'string' => 'Goodbye Cruel World!' ];
        }

        /**
         * @ParamProviders({"provideStrings"})
         */
        public function benchMd5($params)
        {
            hash('md5', $params['string']);
        }
    }

The ``benchMd5`` subject will now be benchmarked with each parameter set.

The param provider can return a set of parameters using any `iterable`.
For example the above could also be returned as an array:

.. code-block:: php

    class HashBench
    {
        public function provideStrings()
        {
            return [
                'hello' => [ 'string' => 'Hello World!' ],
                'goodbye' => [ 'string' => 'Goodbye Cruel World!' ]
            ];
        }
    }

.. warning::

   It should be noted that Generators are consumed completely before the
   subject is executed. If you have a very large data set, it will be read
   completely into memory.

Multiple parameter providers can be used, in which case the data sets will be
combined into a `cartesian product`_ - all possible combinations of the
parameters will be generated:

.. code-block:: php

    class HashBench
    {
        public function provideStrings()
        {
            yield 'hello' => [ 'string' => 'Hello World!' ];
            yield 'goodbye' => [ 'string' => 'Goodbye Cruel World!' ];
        }

        public function provideNumbers()
        {
            yield 'md5' => [ 'algorithm' => 'md5' ];
            yield 'sha1' => [ 'algorithm' => 'sha1' ];
        }

        /**
         * @ParamProviders({"provideStrings", "provideNumbers"})
         */
        public function benchHash($params)
        {
            hash($params['algorithm'], $params['string']);
        }
    }

Will result in the following parameter benchmark scenarios:

.. code-block:: php

    // #0
    ['string' => 'Hello World!', 'algorithm' => 'md5'];

    // #1
    ['string' => 'Goodbye Cruel World!', 'algorithm' => 'md5'[;

    // #2
    ['string' => 'Hello World!', 'algorithm' => 'sha1'];

    // #3
    ['string' => 'Goodbye Cruel World!', 'algorithm' => 'sha1'];

.. _groups:

Groups
------

You can assign benchmark subjects to groups using the ``@Groups`` annotation.

.. code-block:: php

    /**
     * @Groups({"hash"})
     */
    class HashBench
    {
        // ...
    }

The group can then be targeted using the command line interface.

Skipping Subjects
-----------------

You can skip subjects by using the ``@Skip`` annotation:

.. code-block:: php

    class HashBench extends Foobar
    {
        /**
         * @Skip()
         */
        public function testFoobar()
        {
        }
    }

Extending Values
----------------

When working with annotations which accept an array value, you may wish to
extend the values of the same annotation from ancestor classes. This can be
accomplished using the ``extend`` option.

.. code-block:: php

    abstract class AbstractHash
    {
        /**
         * @Groups({"md5"})
         */
        abstract public function benchMd5();
    }

    /**
     * @Groups({"my_hash_implementation"}, extend=true)
     */
    class HashBench extends AbstractHash
    {
        public function benchMd5()
        {
            // ...
        }
    }

The ``benchHash`` subject will now be in both the ``md5`` and
``my_hash_implementation`` groups.

This option is available on all annotations featuring a list of values.

Sleeping
--------

Sometimes it may be necessary to pause between iterations in order to let
the system recover. Use the ``@Sleep`` annotation, specifying the number of
**microseconds** required:

.. code-block:: php

    class HashBench
    {
        /**
         * @Iterations(10)
         * @Sleep(1000000)
         */
        public function benchMd5()
        {
            md5('Hello World');
        }
    }

The above example will pause (sleep) for 1 second *after* each iteration.

.. note::

    This can be overridden using the ``--sleep`` option from the CLI.

.. _time_unit:

Time Units
----------

Specify *output* time units using the ``@OutputTimeUnit`` annotation
(`precision` is optional):

.. code-block:: php

    class HashBench
    {
        /**
         * @Iterations(10)
           @OutputTimeUnit("seconds", precision=3)
         */
        public function benchSleep()
        {
            sleep(2);
        }
    }

The following time units are available:

- ``microseconds``
- ``milliseconds``
- ``seconds``
- ``minutes``
- ``hours``
- ``days``

.. _throughput:
.. _mode:

Throughput Representation
-------------------------

The output mode determines how the measurements are presented, either `time`
or `throughput`. `time` mode is the default and shows the average execution
time of a single :ref:`revolution <revolutions>`. `throughput` shows how many *operations*
are executed within a single time unit:

.. code-block:: php

    class HashBench
    {
        /**
         * @OutputTimeUnit("seconds")
         * @OutputMode("throughput")
         */
        public function benchMd5()
        {
            hash('md5', 'Hello World!');
        }
    }

PHPBench will then render all measurements for `benchMd5` similar to
`363,874.536ops/s`.

Warm Up
-------

Use the ``@Warmup`` annotation to execute any number of revolutions before
actually measuring the revolutions time.

.. code-block:: php

    // ...
    class ReportBench
    {
        // ...

        /**
         * @Warmup(2)
         * @Revs(10)
         */
        public function benchGenerateReport()
        {
            $this->generator->generateMyComplexReport();
        }
    }

As with :ref:`revolutions <revolutions>`, you may also specify an array.

.. _timeouts:

Timeout
-------

Use the ``@Timeout`` annotation to specify the maximum number of seconds
before an iteration timesout and fails. The following example will fail after
0.1 seconds:

.. code-block:: php

    // ...
    class ReportBench
    {
        /**
         * @Timeout(0.1)
         */
        public function benchGenerateReport()
        {
           sleep(1);
        }
    }


.. _assertions:

Assertions
----------

You can annotate your benchmarks with *assertions* which will cause PHPBench
to report failures and exit with a non-zero exit code if they fail.

For example, assert that the mode is less than 100 microseconds:

.. code-block:: php

    /**
     * @Assert("variant.mode < 100 microseconds")
     */
    public function benchFoobar()
    {
        // ...
    }

Assert a throughput greater than 0.25ops/µs:

.. code-block:: php

    /**
     * @Assert("variant.mode > 0.25 ops/microsecond")
     */
    public function benchFoobar()
    {
        // ...
    }

You can also specify assertions from the command line:

.. code-block:: bash

    $ phpbench run --assert='variant.mode > 0.25 ops/microsecond'

See :doc:`assertions` for more information.

.. _cartesian product: https://en.wikipedia.org/wiki/Cartesian_product
