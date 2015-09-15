Quick Start
===========

This quick start assumes that you have installed PHPBench as a dependency
using composer.

Creating and running a benchmark
--------------------------------

You will need some code to benchmark, create a simple class which consumes
*time itself*:

.. code-block:: php

    // lib/TimeConsumer.php
    <?php

    namespace Acme\Demo;

    class TimeConsumer
    {
        public function consume()
        {
            usleep(50000);
        }
    }


In order to benchmark your code you will need to execute that code in a
benchmarking class. Benchmarking classes MUST have the ``Bench`` suffix and
each benchmarking method must be prefixed with ``bench``:

.. code-block:: php

    // bench/TimeConsumer.php
    <?php

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

    $ php vendor/bin/phpbench run bench/TimeConsumer.php --report=default

And you should see some output similar to the following:

.. code-block:: bash

    // OUTPUT HERE

You may have guessed that the code was only executed once. To achieve a better
measurement we should increase the amount of times that the code is executed.
So lets add the ``@revs`` annotation:

.. code-block:: php

    // bench/TimeConsumer.php
    <?php

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
times that the code is executed consecutively within a single measurement.

Currently we only execute the benchmark one time, you should increase the
number of iterations using the ``@iterations``
annotation (either as a replacement or in addition to ``@revs``:

.. code-block:: php

    // bench/TimeConsumer.php
    <?php

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

Now to finish off, lets create a PHPBench configuration file:

.. code-block:: javascript

    {
        "bootstrap": "vendor/autoload.php",
        "path": "bench",
        "reports": {
            "cosumation_of_time": {
                "extends": "simple",
                "title": "The Consumation of Time",
                "description": "Benchmark how long it takes to consume time",
                "sort": ["time": "desc"]
            },
        }
    }

Above you explicitly tell phpbench where to find the bootstrap file for your
code and we tell PHPBench that the benchmarks are contained in the ``bench``
folder. We then create a **new** report, lets run it:

.. code-block:: bash

    $ php vendor/bin/phpbench run --report=consumation_of_time

Note that we did not specify the path to the benchmark file, by default all
benchmarks under the given or configured path will be executed.

This quick start demonstrated some of the features of PHPBench, but there is
more to discover. Read on ...
