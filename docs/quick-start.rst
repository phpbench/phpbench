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
            "phpbench/phpbench": "~1.0@dev"
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

    You may also install PHPBench globally, see the :doc:`installation`
    chapter for more information.

PHPBench should now be installed. Now create two directories, ``benchmarks``
and ``lib`` which we will need futher on:

.. code-block:: bash

    $ mkdir benchmarks
    $ mkdir lib

Creating and running a benchmark
--------------------------------

You will need some code to benchmark, create a simple class in ``lib`` which
consumes *time itself*:

.. code-block:: php

    <?php
    // lib/TimeConsumer.php

    namespace Acme\Demo;

    class TimeConsumer
    {
        public function consume()
        {
            usleep(50000);
        }
    }


In order to benchmark your code you will need to execute that code within
a method of a benchmarking class. Benchmarking classes MUST have the ``Bench``
suffix and each benchmarking method must be prefixed with ``bench``:

.. code-block:: php

    <?php
    // benchmarks/TimeConsumer.php

    use Acme\Demo\TimeConsumer;

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

    // OUTPUT HERE

You may have guessed that the code was only executed once. To achieve a better
measurement we should increase the amount of times that the code is executed.
So lets add the ``@revs`` annotation:

.. code-block:: php

    <?php
    // benchmarks/TimeConsumer.php

    // ...

    class TimeConsumerBench implements Benchmark
    {
        /**
         * @revs 1000
         */
        public function benchConsume()
        {
            // ...
        }
    }

Run the benchmark again and you should notice that the report states that 1000
revolutions were performed. Revolutions in PHPBench represent the number of
times that the code is TODO_ADD_REF_HERE executed consecutively within a
single measurement.

Currently we only execute the benchmark one time, you should increase the
number of iterations using the ``@iterations``
annotation (either as a replacement or in addition to ``@revs``:

.. code-block:: php

    <?php
    // benchmarks/TimeConsumerBench.php

    // ...

    class TimeConsumerBench implements Benchmark
    {
        /**
         * @revs 1000
           @iterations 5
         */
        public function benchConsume()
        {
            // ...
        }
    }

.. note::

    Multiple iterations allow you to make sure that benchmark results are
    stable.

Now when you run the report you should see that it contains 5 rows. One
measurement for each iteration, and each iteration executed the code 1000
times.

.. note::

    You can override the number of iterations and revolutions on the CLI using
    the ``--iterations`` and ``--revs`` options.

At this point it would be better for you to use the ``aggregate`` report
rather than ``default``:

.. code-block:: bash

    $ php vendor/bin/phpbench run bench/TimeConsumer.php --report=default

PHPBench also allows you to customize reports on the command line, try the
following:

.. code-block:: bash

    $ ./vendor/bin/phpbench run bench/TimeConsumerBench.php --report='{"extends": "default", "exclude": ["subject"], "sort": {"time": "desc"}'

Above we configure a new report which extends the ``default`` report that we
have already used, but we exclude the ``subject`` column and sort by time in
descending order.

Now to finish off, lets add the path and new report to the configuration file:

.. code-block:: javascript

    {
        ...
        "path": "benchmarks",
        "reports": {
            "cosumation_of_time": {
                "extends": "simple",
                "title": "The Consumation of Time",
                "description": "Benchmark how long it takes to consume time",
                "sort": ["time": "desc"]
            },
        }
    }

Above you tell PHPBench where the benchmarks are located and you define a new
report, ``consumation_of_time`` with a title, description and sort order. See
the :doc:`reporting` chapter for more information on reporting.

We can now run the new report:

.. code-block:: bash

    $ php vendor/bin/phpbench run --report=consumation_of_time

.. note::

    Note that we did not specify the path to the benchmark file, by default all
    benchmarks under the given or configured path will be executed.

This quick start demonstrated some of the features of PHPBench, but there is
more to discover everything can be found in this manual. Happy benchmarking.
