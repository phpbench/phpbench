XDebug
======

The XDebug extension allows you to easily generate `profiles`_ for your
benchmarks.

.. code-block:: bash

    $ phpbench xdebug:profile examples/HashBench.php --progress=none

    3 profile(s) generated:

        profile/_HashingBenchmark::benchMd5.P0.cachegrind
        profile/_HashingBenchmark::benchSha1.P0.cachegrind
        profile/_HashingBenchmark::benchSha256.P0.cachegrind

The command is very similar to the standard ``run`` command with the
difference that only single iterations are performed.

A single profile is generated for each subject in the benchmark and placed in
the directory ``profile`` by default.

.. note::

    XDebug needs to be installed, however it does NOT need to be activated by
    default. PHPBench will automatically try and load and configure the
    extension even if it is disabled.

Installation
------------

The XDebug extension is bundled with PHPBench, it just needs to be activated:

.. code-block:: javascript

    {
       "extensions": {
           "PhpBench\\Extensions\\XDebug\\XDebugExtension"
       }
    }

Alternatively you can activate it directly from the CLI using the
``extension`` option:

.. code-block:: php

    $ phpbench xdebug:profile examples/HashBench.php --extension="PhpBench\\Extensions\\XDebug\\XDebugExtension"

Visualing with a GUI
--------------------

It is possible to automatically launch a GUI for each of the profiles using
the ``--gui`` option. By default PHPBench will attempt to locate the
``kcachegrind`` executable. If you do not have ``kcachegrind`` you can specify
a different executable using the ``--gui-bin`` option.

.. image:: ../images/profile.png

.. _profiles: http://xdebug.org/docs/profiler
