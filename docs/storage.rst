Storage and Querying
====================

PHPBench allows benchmarking results to be persisted using a configured
storage driver. You can inspect the results with either the ``show`` or
``report`` commands.

Configuring a Storage Driver
----------------------------

PHPBench will use XML storage by default, which is fine for most purposes. If
you want advanced functionality (e.g. the ability to query benchmarks) then
you can install the :doc:`Doctrine DBAL extension<extensions/dbal>`.

The XML storage driver will place benchmarks in a folder called ``_storage``
by default, this can be changed in the configuration as follows:

.. code-block:: javascript

    {
        "xml_storage_path": "_storage"
    }

Storing Results
---------------

In order to store benchmarking runs you simply need to give ``--store`` option
when running your benchmarks:

.. code-block:: bash

    $ phpbench run --store

You can tag runs with the ``--tag`` option to make them easier to reference
(more on this later):

.. code-block:: bash

    $ phpbench run --store --tag=my_tag_name

Tags must be alpha-numeric and may also contain underscores.

Viewing the History
-------------------

Once you have stored some benchmark runs you can use the history command to
see what you have got:

.. code-block:: bash

    $ phpbench log
    run 875c827946204db23eadd4b10e76b7189e10dde2
    Date:    2016-03-19T09:46:52+01:00
    Branch:  git_log
    Tag: <none>
    Scale:   1 subjects, 60 iterations, 120 revolutions
    Summary: (best [mean] worst) = 433.467 [988.067] 504.600 (μs)
             ⅀T: 59,284.000μs μRSD/r: 9.911%

    run 9d38a760e6ebec0a466c80f148264a7a4bb7a203
    Date:    2016-03-19T09:46:39+01:00
    Branch:  git_log
    Tag: <none>
    Scale:   1 subjects, 30 iterations, 30 revolutions
    Summary: (best [mean] worst) = 461.800 [935.720] 503.300 (μs)
             ⅀T: 28,071.600μs μRSD/r: 4.582%

    ...

Report Generation
-----------------

You can report on a single given run ID using the ``show`` command:

.. code-block:: bash

    $ phpbench show 9d38a760e6ebec0a466c80f148264a7a4bb7a203

You may also specify a different report with the ``--report`` option. In order
to compare two or more reports, you should use the ``report`` command as
detailed in the following section.

Pseudo UUIDs
------------

UUIDs are difficult to work with. Phpbench allows you to use a number of
"pseudo" uuids.

``latest``
~~~~~~~~~~

It is possible to specify "meta" UUIDs, such as ``latest``:

.. code-block:: bash

    $ phpbench show latest

And also you may use the ``-<n>`` suffix to view the "nth" entry in
the history from the latest:

.. code-block:: bash

    $ phpbench show latest-1

Would show the second latest entry. Meta UUIDs can be used anywhere where you
would normally specify a UUID, including queries.

``tag:``
~~~~~~~~

Allows you to reference a tagged run. If you store a suite:

.. code-block:: bash

    $ phpbench run --store --tag=my_tag

Then you can reference it with ``tag:my_tag``

.. code-block:: bash

    $ phpbench show tag:my_tag

Querying
--------

.. important::

    The XML storage driver does not support querying, if you require this
    functionality install the :doc:`Doctrine DBAL extrension<extensions/dbal>`.

PHPBench uses a query language very similar to that of MongoDB. A simple
example:

.. code-block:: bash

    $ phpbench report --report=aggregate --query='subject: "benchMd5", run: 239"'

Would show the results in an aggregate report for the benchmarking subject
``benchMd5`` from run ``239``.

A more complex example:

.. code-block:: bash

    $ phpbench report --report=aggregate --query='$and: [ { subject: "benchMd5" }, { date: { $gt: "2016-02-09" } } ]'

This would generate a suite collection containing all the ``benchMd5``
subjects created after ``2016-02-09``.

Logical Operators
~~~~~~~~~~~~~~~~~

Logical operators must have as a value an array of constraints.

$and
""""

Return only the records which meet both of the given constraints::

    $and: [ { field1: "value1" }, { field2: "value2" } ]

$or
""""

Return only the records which meet at least one of the given constraints::

    $or: [ { field1: "value1" }, { field2: "value2" } ]

Logical Comparisons
~~~~~~~~~~~~~~~~~~~

$eq
"""

Note that that equality is assumed if the value for a field is a scalar::

    subject: "benchMd5"

The verbose equality comparison would be::

    subject: { $eq: "benchMd5" }

$neq
""""

Non-equality comparison::

    run: { $neq: 12 }

$gt, $gte
"""""""""

Greater than and greater than or equal to comparisons::

    date: { $gt: "2016-02-10" }

$lt, $lte
"""""""""

Greater than and greater than or equal to comparisons::

    date: { $lt: "2016-02-10" }

$in
"""

Matches when the field value matches any one of the given values::

    run: { $in: [ 10, 11, 12 ] }

$regex
""""""

Provides regular expression capabilities for pattern matching strings in
queries::

    benchmark: { $regex: "FooBarBench" }

    benchmark: { $regex: "Foo.*Bench" }

Fields
~~~~~~

The following fields are currently available for querying:

- **benchmark**: The benchmark class name.
- **subject**: The subject name (e.g. ``benchMd5``)
- **revs**: The number of revolutions.
- **date**: The date.
- **run**: The run ID (as inferred from the ``phpbench history`` command).
- **group**: The group name.
- **param**: Query a parameter value, parameter name in square brackets.

Parameters may be queried with the `param` field - the parameter name should
be enclosed in square brackets as follows::

    param[nb_elements]: 10

    param[points]: { $gt: 50 }

.. _archive:

Archiving
---------

Archiving provides a way to export and reimport data from and to the
configured storage. This allows you to:

- Backup your results (for example to a GIT repository).
- Migrate to other storage drivers.

By default PHPBench is configured to use an ``XML`` archiver, which will dump
results to a directory in the current working directory, ``_archive``.

To archive::

    $ phpbench archive

To restore::

    $ phpbench archive --restore 

Both operations are idempotent - they will skip any existing records.

You may configure a different archiver in the configuration:

.. code-block:: javascript

    {
        "archiver": "xml"
    }
