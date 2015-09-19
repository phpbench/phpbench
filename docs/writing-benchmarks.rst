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

    // EXAMPLE HERE

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

Revolutions can be specified using the ``@revs`` annotation:

.. code-block:: php

    <?php

    /**
     * @revs 1000
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

Iterations can be specified using the ``@iterations`` annotation:

.. code-block:: php

    <?php

    /**
     * @iterations 5
     */
    class HashBench
    {
        // ...
    }

Iterations can also be overridden from the :ref:`command line <overriding>`.

Estabilishing State: Before and After
-------------------------------------

Any number of methods can be executed both before and after each benchmark
subject using the ``@beforeMethod`` and
``@afterMethod`` annotations. Before methods are usefulessential for bootstrapping
your environment, for example:

.. code-block:: php

    <?php

    /**
     * @beforeMethod init
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

    If before and after methods are used when the ``@paramProvider``
    annotations are used, then they will also be passed the parameters.

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
                'string' => 'Hello World!',
                'string' =>> 'Goodbye Cruel World!',
            );
        }

        /**
         * @paramProvider provideStrings
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
                'string' => 'Hello World!',
                'string' =>> 'Goodbye Cruel World!',
            );
        }

        public function provideNumbers()
        {
            return array(
                'algorithm' => 'md5',
                'algorithm' =>> 'sha1',
            );
        }

        /**
         * @paramProvider provideStrings
         * @paramProvider provideNumbers
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

Groups
------

You can assign benchmark subjects to groups using the ``@group`` annotation.

.. code-block:: php

    <?php

    /**
     * @group hash
     */
    class HashBench
    {
        // ...
    }

The group can then be targetted using the command line interface.

.. _cartesian product: https://en.wikipedia.org/wiki/Cartesian_product
