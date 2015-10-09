Writing Benchmarks
==================

Benchmark classes have the following characteristics:

- The class and filename must be the same.
- The class name must end with ``Bench``.
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

    $ phpbench run HashBench.php
    PhpBench 0.5. Running benchmarks.
    Using configuration file: /home/daniel/www/phpbench-tutorial/phpbench.json

    ..
    Done (2 subjects, 2 iterations) in 0.07s

.. note::

    The above command does not generate a report, add ``--report=default`` to
    view something useful.

PHPBench reads docblock annotations in the benchmark class. Annotations can be
placed in the class docblock, or on individual methods docblocks.

.. _revolutions:

Improving Precision: Revolutions
--------------------------------

When testing units of code where microsecond accuracy is important, it is
necessary to increase the number of *revolutions* performed by the
benchmark runner. The term "revolutions" (invented here) refers to the number
of times the benchamark is executed consecutively within a single time
measurement.

We can arrive at a more accurate measurement by determining the average time
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

Revolutions can also be overridden from the :ref:`command line <overriding>`.

.. _iterations:

Improving Stability: Iterations
-------------------------------

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

Iterations can also be overridden from the :ref:`command line <overriding>`.

Estabilishing State: Before and After
-------------------------------------

Any number of methods can be executed both before and after each benchmark
subject using the ``@BeforeMethods`` and
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
            return array(
                array(
                    'hello' => 'Hello World!',
                    'goodbye' => 'Goodbye Cruel World!',
                )
            );
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

Multiple parameter providers can be used, in which case the data sets will be
combined into a `cartesian product`_ - all possible combinations of the
parameters will be generated, for example:

.. code-block:: php

    <?php

    class HashBench
    {
        public function provideStrings()
        {
            return array(
                array(
                    'string' => 'Hello World!',
                ),
                array(
                    'string' => 'Goodbye Cruel World!',
                ),
            );
        }

        public function provideNumbers()
        {
            return array(
                array(
                    'algorithm' => 'md5',
                ),
                array(
                    'algorithm' => 'sha1',
                ),
            );
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
    array('string' => 'Hello World!', 'algorithm' => 'md5');

    // #1
    array('string' => 'Goodbye Cruel World!', 'algorithm' => 'md5');

    // #2
    array('string' => 'Hello World!', 'algorithm' => 'sha1');

    // #3
    array('string' => 'Goodbye Cruel World!', 'algorithm' => 'sha1');

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

The group can then be targetted using the command line interface.

Skiping Subjects
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

.. _cartesian product: https://en.wikipedia.org/wiki/Cartesian_product
