Quick Start
===========

If you are familiar with PHPUnit then PHPBench will look very familiar to you.
If you are not familiar with PHPUnit then do not fear, its not that difficult.

This quick start assumes that you have installed PHPBench as a dependency
using composer.

Creating and running a benchmark
--------------------------------

You will need some code to benchmark, create a simple class which consumes
time itself:

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
``Benchmark`` class. Benchmark classes need to implement the ``PhpBench\\Benchmark``
interface:

.. code-block:: php

    // bench/TimeConsumer.php
    <?php

    namespace Acme\Demo\Benchmark;

    class TimeConsumerBench implements Benchmark
    {
        public function benchConsume()
        {
           $consumer = new TimeConsumer();
           $consumer->consume();
        }
    }

Now you can execute the benchmark as follows:

.. code-block:: bash

    $ php vendor/bin/phpbench run bench/TimeConsumer.php --report=simple

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

If you want to measure each seperate execution you can use the ``@iterations``
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

Now when you run the report you should see that it contains 5 rows. One
measurement for each iteration, and each iteration executed the code 1000
times.

.. note::

    You can override the number of iterations and revolutions on the CLI using
    the ``--iterations`` and ``--revs`` options.

PHPBench also allows you to customize reports on the command line, so lets try
something:

.. code-block:: bash

    $ ./vendor/bin/phpbench run bench/TimeConsumerBench.php --report='{"extends": "simple", "exclude": ["subject"], "sort": ["time": "desc"]}'

Above we create a new report on-the-fly which extends the ``simple`` report
that we have already used, but we exclude the ``subject`` column and sort by
time in descending order.

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
code (the value above is the default and can actually be ommitted) and we tell
PHPBench that the benchmarks are contained in the ``bench`` folder. We then
create a **new** report, lets run it:

.. bash::

    $ php vendor/bin/phpbench run --report=consumation_of_time

Note that we did not specify the path to the benchmark file, by default all
benchmarks under the given or configured path will be executed.

This quick start demonstrated some of the features of PHPBench, but there is
more to discover. Read on ...
