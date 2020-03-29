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

    <?php
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
    PhpBench 0.8.0-dev. Running benchmarks.

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

Improving Precision: Revolutions
--------------------------------

When testing units of code where microsecond accuracy is important, it is
necessary to increase the number of *revolutions* performed by the
benchmark runner. The term "revolutions" (invented here) refers to the number
of times the benchmark is executed consecutively within a single time
measurement.

We can arrive at a more accurate measurement by determining the mean time
from multiple revolutions (i.e. *time / revolutions*) than we could with a
single revolution. In other words, more revolutions means more precision.

Revolutions can be specified using the ``@Revs`` annotation:

.. code-block:: php

    <?php

    /**
     * @Revs(1000)
     */
    class HashBench
    {
        // ...
    }

You may also specify an array:

.. code-block:: php

    <?php

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

Verifying and Improving Stability: Iterations
---------------------------------------------

Iterations represent the number of times we will perform the benchmark
(including all the revolutions). Contrary to revolutions, a time reading will
be taken for *each iteration*.

By looking at the separate time measurement of each iteration we can determine
how *stable* the readings are. The less the measurements differ from each
other, the more stable the benchmark is, and the more you can trust the results.

.. note::

    In a *perfect* environment the readings would all be *exactly* the same -
    but such an environment is unlikely to exist 

Iterations can be specified using the ``@Iterations`` annotation:

.. code-block:: php

    <?php

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

Subject (runtime) State: Before and After
-----------------------------------------

Any number of methods can be executed both before and after each benchmark
**subject** using the ``@BeforeMethods`` and
``@AfterMethods`` annotations. Before methods are useful for bootstrapping
your environment, for example:

.. code-block:: php

    <?php

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

Benchmark (external) State: Before and After
--------------------------------------------

Sometimes you will want to perform actions which establish an *external*
state. For example, creating or populating a database, creating files, etc.

This can be achieved by creating **static** methods within your benchmark
class and adding the ``@BeforeClassMethods`` and ``@AfterClassMethods``:

These methods will be executed by the runner once per benchmark class.

.. code-block:: php

    <?php

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

Parameter sets can be provided to benchmark subjects. For example:

.. code-block:: php

    <?php

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
For example the above could also be retuned as an array:

.. code-block:: php

    <?php

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
parameters will be generated, for example:

.. code-block:: php

    <?php

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

    <?php

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

    <?php

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

    <?php

    class HashBench extends Foobar
    {
        /**
         * @Skip()
         */
        public function testFoobar()
        {
        }
    }

Extending Existing Array Values
-------------------------------

When working with annotations which accept an array value, you may wish to
extend the values of the same annotation from ancestor classes. This can be
accomplished using the ``extend`` option.

.. code-block:: php

    <?php

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

This option is available on all array valued (plural) annotations.

Recovery Period: Sleeping
--------------------------

Sometimes it may be necessary to pause between iterations in order to let
the system recover. Use the ``@Sleep`` annotation, specifying the number of
**microseconds** required:

.. code-block:: php

    <?php

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

Microseconds to Minutes: Time Units
-----------------------------------

If you have benchmarks which take seconds or even minutes to execute then the
default time unit, microseconds, is going to be far more visual precision than you
need and will only serve to make the results more difficult to interpret.

You can specify *output* time units using the ``@OutputTimeUnit``
annotation (`precision` is optional):

.. code-block:: php

    <?php

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

Mode: Throughput Representation
--------------------------------

The output mode determines how the measurements are presented, either `time`
or `throughput`. `time` mode is the default and shows the average execution
time of a single :ref:`revolution <revolutions>`. `throughput` shows how many *operations*
are executed within a single time unit:

.. code-block:: php

    <?php

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

Warming Up: Getting ready for the show
--------------------------------------

In some cases, it might be a good idea to execute a revolution or two before
performing the revolutions time measurement. 

For example, when benchmarking something that uses an class autoloader, the
first revolution will always be slower because the autoloader will not to be
called again.

Use the ``@Warmup`` annotation to execute any number of revolutions before
actually measuring the revolutions time.


.. code-block:: php

    <?php

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

Timeout: Bailing when things take too long
------------------------------------------

Use the ``@Timeout`` annotation to specify the maximum number of seconds
before an iteration timesout and fails. The following example will fail after
0.1 seconds:

.. code-block:: php

    <?php

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

.. warning::

    Assertions are absolute, benchmarks are relative to the environment they
    are running in. 
    
    If you use them in a continuous integration environment the stability of
    your build will depend on the state of the environment, you can prevent
    failing builds with the `--tolerate-failure` option.

Assertions allow you to specify what a valid range is for a given statistic,
for example, "the mean must be less than 10".

.. code-block:: php

    <?php

    // ...
    class AssertiveBench
    {
        // ...

        /**
         * @Assert(stat="mean", value="10")
         */
        public function benchGenerateReport()
        {
            // ...
        }
    }

By default the comparator is ``<`` (less than), you can also specify ``>``
using the ``comparator`` key:

.. code-block:: php

    <?php

    class AssertiveBench
    {
        // ...

        /**
         * @Assert(stat="mean", value="10", comparator=">")
         */
        public function benchGenerateReport()
        {
            // ...
        }
    }

The default time unit for assertions is microseconds, but you can specify any
supported time unit and you can also change the mode to ``throughput``:


.. code-block:: php

    <?php

    class AssertiveBench
    {
        // ...

        /**
         * @Assert(stat="mean", value="10", comparator=">", time_unit="milliseconds", mode="throughput")
         */
        public function benchGenerateReport()
        {
            // ...
        }
    }

The above will assert that an average of more than 10 operations are completed
in a millisecond. See :ref:`time_unit` and :ref:`mode` for more information.

For more information about assertions see :doc:`assertions-asserters`.

.. _cartesian product: https://en.wikipedia.org/wiki/Cartesian_product
.. _Relative standard deviation: https://en.wikipedia.org/wiki/Coefficient_of_variation
