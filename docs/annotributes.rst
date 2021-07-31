Annotributes
============

Configure your benchmarks with **Annotations** or, if you have PHP 8, **Attributes**. Note that some
of these settings (e.g. :ref:`configuration_runner_revs`,
:ref:`configuration_runner_iterations`) can be set globaly in the
:doc:`configuration`.

.. contents::
    :depth: 1
    :local:


.. _metadata_revolutions:

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

.. tabs::

    .. tab:: Annotations

        .. codeimport:: ../examples/Annotations/AnnotatedBench.php
          :language: php
          :sections: all,revs,benchTime

    .. tab:: Attributes

        .. codeimport:: ../examples/Attributes/AttributedBench.php
          :language: php
          :sections: all,revs,benchTime

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

.. _metadata_iterations:

Iterations
----------

Iterations specify how many samples should be taken - i.e. how many times we
run the :ref:`revolutions <metadata_revolutions>` and capture time and memory information (for example). 

By looking at the separate time measurement of each iteration we can determine
how *stable* the readings are. The less the measurements differ from each
other, the more stable the benchmark.

.. note::

    In a *perfect* environment the readings would all be *exactly* the same -
    but such an environment is unlikely to exist 

Iterations can be specified using the ``@Iterations`` annotation:

.. tabs::

    .. tab:: Annotations

        .. codeimport:: ../examples/Annotations/AnnotatedBench.php
          :language: php
          :sections: all,iterations,benchTime

    .. tab:: Attributes

        .. codeimport:: ../examples/Attributes/AttributedBench.php
          :language: php
          :sections: all,iterations,benchTime

As with :ref:`revolutions <metadata_revolutions>`, you may also specify an array.

Iterations can also be overridden from the :ref:`command line
<overriding_iterations_and_revolutions>`.

You can instruct PHPBench to continuously run the iterations until the
deviation of each iteration fits within a given margin of error by using the
``--retry-threshold``. See :ref:`metadata_retry_threshold` for more information.

Benchmark Hooks
---------------

Method hooks
~~~~~~~~~~~~

Any number of methods can be executed both before and after each benchmark
**subject** using the ``@BeforeMethods`` and
``@AfterMethods`` annotations. Before methods are useful for bootstrapping
your environment:

.. tabs::

    .. tab:: Annotations

        .. codeimport:: ../examples/Annotations/AnnotatedBench.php
          :language: php
          :sections: all,beforeMethods,afterMethods,benchTime

    .. tab:: Attributes

        .. codeimport:: ../examples/Attributes/AttributedBench.php
          :language: php
          :sections: all,beforeMethods,afterMethods,benchTime

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

.. tabs::

    .. tab:: Annotations

        .. codeimport:: ../examples/Annotations/AnnotatedBench.php
          :language: php
          :sections: all,beforeClassMethods,afterClassMethods,benchTime

    .. tab:: Attributes

        .. codeimport:: ../examples/Attributes/AttributedBench.php
          :language: php
          :sections: all,beforeClassMethods,afterClassMethods,benchTime

.. note::

    These methods are static and are executed in a process that is separate
    from that from which your iterations will be executed. Therefore **state
    will not be carried over to your iterations!**.

.. _metadata_parameters:

Parameterized Benchmarks
------------------------

Parameter sets can be provided to benchmark subjects:

.. tabs::

    .. tab:: Annotations

        .. codeimport:: ../examples/Annotations/AnnotatedBench.php
          :language: php
          :sections: all,paramProviders

    .. tab:: Attributes

        .. codeimport:: ../examples/Attributes/AttributedBench.php
          :language: php
          :sections: all,paramProviders

The `benchMd5` subject will now be benchmarked with each parameter set.

The param provider can return a set of parameters using any `iterable`.
For example the above could also be returned as an array:

.. tabs::

    .. tab:: Annotations

        .. codeimport:: ../examples/Annotations/AnnotatedBench.php
          :language: php
          :sections: all,paramIterable

    .. tab:: Attributes

        .. codeimport:: ../examples/Attributes/AttributedBench.php
          :language: php
          :sections: all,paramIterable

.. warning::

   It should be noted that Generators are consumed completely before the
   subject is executed. If you have a very large data set, it will be read
   completely into memory.

Multiple parameter providers can be used, in which case the data sets will be
combined into a `cartesian product`_ - all possible combinations of the
parameters will be generated:

.. tabs::

    .. tab:: Annotations

        .. codeimport:: ../examples/Annotations/AnnotatedBench.php
          :language: php
          :sections: all,paramMultiple

    .. tab:: Attributes

        .. codeimport:: ../examples/Attributes/AttributedBench.php
          :language: php
          :sections: all,paramMultiple

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

.. _metadata_groups:

Groups
------

You can assign benchmark subjects to groups using the ``@Groups`` annotation.

.. tabs::

    .. tab:: Annotations

        .. codeimport:: ../examples/Annotations/AnnotatedBench.php
          :language: php
          :sections: all,benchTime,groups

    .. tab:: Attributes

        .. codeimport:: ../examples/Attributes/AttributedBench.php
          :language: php
          :sections: all,benchTime,groups

The group can then be targeted using the command line interface.

Skipping Subjects
-----------------

You can skip subjects by using the ``@Skip`` annotation:

.. tabs::

    .. tab:: Annotations

        .. codeimport:: ../examples/Annotations/AnnotatedBench.php
          :language: php
          :sections: all,skip

    .. tab:: Attributes

        .. codeimport:: ../examples/Attributes/AttributedBench.php
          :language: php
          :sections: all,skip

Sleeping
--------

Sometimes it may be necessary to pause between iterations in order to let
the system recover. Use the ``@Sleep`` annotation, specifying the number of
**microseconds** required:

.. tabs::

    .. tab:: Annotations

        .. codeimport:: ../examples/Annotations/AnnotatedBench.php
          :language: php
          :sections: all,benchTime,sleep

    .. tab:: Attributes

        .. codeimport:: ../examples/Attributes/AttributedBench.php
          :language: php
          :sections: all,benchTime,sleep

The above example will pause (sleep) for 1 millisecond *after* each iteration.

.. note::

    This can be overridden using the ``--sleep`` option from the CLI.

.. _metadata_time_unit:

Time Units
----------

Specify *output* time units using the ``@OutputTimeUnit`` annotation
(`precision` is optional):

.. tabs::

    .. tab:: Annotations

        .. codeimport:: ../examples/Annotations/AnnotatedBench.php
          :language: php
          :sections: all,benchTime,outputTimeUnit

    .. tab:: Attributes

        .. codeimport:: ../examples/Attributes/AttributedBench.php
          :language: php
          :sections: all,benchTime,outputTimeUnit

The following time units are available:

- ``microseconds``
- ``milliseconds``
- ``seconds``
- ``minutes``
- ``hours``
- ``days``

.. _metadata_throughput:
.. _metadata_mode:

Throughput Representation
-------------------------

The output mode determines how the measurements are presented, either `time`
or `throughput`. `time` mode is the default and shows the average execution
time of a single :ref:`revolution <metadata_revolutions>`. `throughput` shows how many *operations*
are executed within a single time unit:

.. tabs::

    .. tab:: Annotations

        .. codeimport:: ../examples/Annotations/AnnotatedBench.php
          :language: php
          :sections: all,benchTime,outputMode

    .. tab:: Attributes

        .. codeimport:: ../examples/Attributes/AttributedBench.php
          :language: php
          :sections: all,benchTime,outputMode

PHPBench will then render all measurements for `benchTimeItself` similar to
`363,874.536ops/s`.

.. _metadata_warmup:

Warm Up
-------

Use the ``@Warmup`` annotation to execute any number of revolutions before
actually measuring the revolutions time.

.. tabs::

    .. tab:: Annotations

        .. codeimport:: ../examples/Annotations/AnnotatedBench.php
          :language: php
          :sections: all,benchTime,warmup

    .. tab:: Attributes

        .. codeimport:: ../examples/Attributes/AttributedBench.php
          :language: php
          :sections: all,benchTime,warmup

As with :ref:`revolutions <metadata_revolutions>`, you may also specify an array.

.. _metadata_timeout:

Timeout
-------

Use the ``@Timeout`` annotation to specify the maximum number of seconds
before an iteration timesout and fails. The following example will fail after
0.1 seconds:

.. tabs::

    .. tab:: Annotations

        .. codeimport:: ../examples/Annotations/AnnotatedBench.php
          :language: php
          :sections: all,benchTime,timeout

    .. tab:: Attributes

        .. codeimport:: ../examples/Attributes/AttributedBench.php
          :language: php
          :sections: all,benchTime,timeout

.. _metadata_assertions:

Assertions
----------

You can annotate your benchmarks with *assertions* which will cause PHPBench
to report failures and exit with a non-zero exit code if they fail.

For example, assert that the :ref:`KDE mode<expr_func_mode>` is less than 200 microseconds:

.. tabs::

    .. tab:: Annotations

        .. codeimport:: ../examples/Annotations/AnnotatedBench.php
          :language: php
          :sections: all,benchTime,assert

    .. tab:: Attributes

        .. codeimport:: ../examples/Attributes/AttributedBench.php
          :language: php
          :sections: all,benchTime,assert

You can also specify assertions from the command line:

.. code-block:: bash

    $ phpbench run --assert='mode(variant.time.avg) < 10 hours'

See :doc:`guides/assertions` for more information.

.. _cartesian product: https://en.wikipedia.org/wiki/Cartesian_product

.. _metadata_format:

Format
------

Override how the variant results are formatted in the progress output.

.. tabs::

    .. tab:: Annotations

        .. codeimport:: ../examples/Annotations/AnnotatedBench.php
          :language: php
          :sections: all,benchTime,format

    .. tab:: Attributes

        .. codeimport:: ../examples/Attributes/AttributedBench.php
          :language: php
          :sections: all,benchTime,format

You can also specify assertions from the command line:

.. code-block:: bash

    $ phpbench run --format='"This is my time: " ~ mode(variant.time.avg)'

See :doc:`expression` for details on using the expressio language.

.. _metadata_retry_threshold:

RetryThreshold
--------------

Set the retry threshold (the deviation beyond which a sample will be
considered invalid and retried).

Use to create more stable sets of iterations.

.. tabs::

    .. tab:: Annotations

        .. codeimport:: ../examples/Annotations/AnnotatedBench.php
          :language: php
          :sections: all,retrythreshold,benchTime

    .. tab:: Attributes

        .. codeimport:: ../examples/Attributes/AttributedBench.php
          :language: php
          :sections: all,retrythreshold,benchTime

Good values are generally 10 or less, the above threshold is 20 because the
examples are executed in the continuous integration environment and may cause
delays.

.. _cartesian product: https://en.wikipedia.org/wiki/Cartesian_product
