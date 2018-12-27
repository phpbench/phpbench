Quick Start
===========

This tutorial will walk you through creating a typical, simple, project that
uses PHPBench as a dependency. You may also install PHPBench globally, see the
:doc:`installing` chapter for more information.

You may skip various sections according to your needs and use this as a general
reference.

Create your project
-------------------

Create a directory for the tutorial:

.. code-block:: bash

    $ mkdir phpbench-tutorial

And create the following Composer_ file within it:

.. code-block:: javascript

    {
        "name": "acme/phpbench-test",
        "require-dev": {
            "phpbench/phpbench": "^1.0@dev"
        },
        "autoload": {
            "psr-4": {
                "Acme\\": "lib"
            }
        }
    }

Now perform a Composer install:

.. code-block:: bash

    $ composer install

.. note::

    You may also install PHPBench globally, see the :doc:`installing`
    chapter for more information.

PHPBench should now be installed. Now create two directories, ``benchmarks``
and ``lib`` which we will need further on:

.. code-block:: bash

    $ mkdir benchmarks
    $ mkdir lib

PHPBench configuration
----------------------

In order for PHPBench to be able to autoload files from your library, you
should specify the path to your bootstrap file (i.e. ``vendor/autoload.php``).
This can be done in the PHPBench :doc:`configuration <configuration>`.

Create the file ``phpbench.json`` in the projects root directory:

.. code-block:: javascript

    {
        "bootstrap": "vendor/autoload.php"
    }

.. note::

    PHPBench does not **require** a bootstrap (or a configuration file for
    that matter). You may omit it if you do not need autoloading, or you want
    to include files manually.

.. warning::

    Some PHP extensions such as Xdebug will affect the performance of your
    benchmark subjects and you may want to disable them, see :ref:`Disabling
    the PHP INI file <configuration_disable_php_ini>`.

Creating and running a benchmark
--------------------------------

You will need some code to benchmark, create a simple class in ``lib`` which
consumes *time itself*:

.. code-block:: php

    <?php

    namespace Acme;

    class TimeConsumer
    {
        public function consume()
        {
            usleep(100);
        }
    }


In order to benchmark your code you will need to execute that code within
a method of a benchmarking class. Benchmarking classes MUST have the ``Bench``
suffix and each benchmarking method must be prefixed with ``bench``.

Create the following class in the ``benchmarks`` directory:

.. code-block:: php

    <?php

    use Acme\TimeConsumer;

    class TimeConsumerBench
    {
        public function benchConsume()
        {
           $consumer = new TimeConsumer();
           $consumer->consume();
        }
    }

Now you can execute the benchmark as follows:

.. code-block:: bash

    $ ./vendor/bin/phpbench run benchmarks/TimeConsumerBench.php --report=default

And you should see some output similar to the following:

.. code-block:: bash

    PhpBench 0.8.0-dev. Running benchmarks.

    \TimeConsumerBench

        benchConsume                  I0 P0         [μ Mo]/r: 173.00μs   [μSD μRSD]/r: 0.00μs 0.00%

    1 subjects, 1 iterations, 1 revs, 0 rejects
    ⅀T: 173μs μSD/r 0.00μs μRSD/r: 0.00%
    min [mean mode] max: 173.00 [173.00 1732.00] 173.00 (μs/r)

    +-------------------+---------------+-------+--------+------+------+-----+----------+------------+---------+-------+
    | benchmark         | subject       | group | params | revs | iter | rej | mem      | time       | z-score | diff  |
    +-------------------+---------------+-------+--------+------+------+-----+----------+------------+---------+-------+
    | TimeConsumerBench | benchConsume  |       | []     | 1    | 0    | 0   | 265,936b | 173.0000μs | 0.00σ   | 1.00x |
    +-------------------+---------------+-------+--------+------+------+-----+----------+------------+---------+-------+

You may have guessed that the code was only executed once (as indicated by the
``revs`` column). To achieve a better measurement we should increase the
number of times that the code is consecutively executed.

.. code-block:: php

    <?php

    // ...

    class TimeConsumerBench
    {
        /**
         * @Revs(1000)
         */
        public function benchConsume()
        {
            // ...
        }
    }

Run the benchmark again and you should notice that the report states that 1000
revolutions were performed. :ref:`Revolutions <revolutions>` in PHPBench
represent the number of times that the code is executed consecutively within a
single measurement.

Currently we only execute the benchmark subject a single time, to verify the
result you should increase the number of :ref:`iterations <iterations>` using
the ``@Iterations`` annotation (either as a replacement or in addition to
``@Revs``:

.. code-block:: php

    <?php

    // ...

    class TimeConsumerBench
    {
        /**
         * @Revs(1000)
         * @Iterations(5)
         */
        public function benchConsume()
        {
            // ...
        }
    }

Now when you run the report you should see that it contains 5 rows. One
measurement for each iteration, and each iteration executed the code 1000
times.

.. note::

    You can override the number of iterations and revolutions on the CLI using
    the ``--iterations`` and ``--revs`` options.

At this point it would be better for you to use the ``aggregate`` report
rather than ``default``:

.. code-block:: bash

    $ php vendor/bin/phpbench run benchmarks/TimeConsumerBench.php --report=aggregate

Increase Stability
------------------

You will see the columns `stdev` and `rstdev`. `stdev` is the `standard
deviation`_ of the set of iterations and `rstdev` is `relative standard
deviation`_.

Stability can be inferred from `rstdev`, with 0% being the best and anything
about 2% should be treated as suspicious.

To increase stability you can use the ``--retry-threshold`` to automatically
:ref:`repeat the iterations <retry_threshold>` until the `diff` (the
percentage difference from the lowest measurement) fits within a given
threshold:

.. note:

    You can see the `diff` value for each iteration in the `default` report.

.. code-block:: bash

    $ php vendor/bin/phpbench run benchmarks/TimeConsumerBench.php --report=aggregate --retry-threshold=5

.. warning::

    Lower values for ``retry-threshold``, depending on the stability of your
    system,  generally lead to increased total benchmarking time.

Customize Reports
-----------------

PHPBench also allows you to customize reports on the command line, try the
following:

.. code-block:: bash

    $ ./vendor/bin/phpbench run benchmarks/TimeConsumerBench.php --report='{"extends": "aggregate", "cols": ["subject", "mode"]}'

Above we configure a new report which extends the ``default`` report that we
have already used, but we use only the ``subject`` and ``mode`` columns.
A full list of all the options for the default reports can be found in the
:doc:`report-generators` chapter.

Configuration
-------------

Now to finish off, lets add the path and new report to the configuration file:

.. code-block:: javascript

    {
        ...
        "path": "benchmarks",
        "reports": {
            "consumation_of_time": {
                "extends": "default",
                "title": "The Consumation of Time",
                "description": "Benchmark how long it takes to consume time",
                "cols": [ "subject", "mode" ]
            }
        }
    }

.. warning::

    JSON files are very strict - be sure not to have commas after the final
    elements in arrays or objects!

Above you tell PHPBench where the benchmarks are located and you define a new
report, ``consumation_of_time`` with a title, description and sort order.

We can now run the new report:

.. code-block:: bash

    $ php vendor/bin/phpbench run --report=consumation_of_time

.. note::

    Note that we did not specify the path to the benchmark file, by default all
    benchmarks under the given or configured path will be executed.

This quick start demonstrated some of the features of PHPBench, but there is
more to discover everything can be found in this manual. Happy benchmarking.

.. _composer: http://getcomposer.org
.. _Relative standard deviation: https://en.wikipedia.org/wiki/Coefficient_of_variation
.. _standard deviation: https://en.wikipedia.org/wiki/Standard_deviation
