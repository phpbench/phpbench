Storage and Querying
====================

PHPBench allows benchmarking results to be persisted using a configured
storage driver. Persisted results can later be against using the ``report``
command.

Configuring a Storage Driver
----------------------------

By default PHPBench does not have any storage drivers enabled. You can however
easily enable the bundled :doc:`sqlite extension <extensions/sqlite>` in your configuration file:

.. code-block:: javascript

    {
        "storage": "sqlite",
        "extensions": [
            "PhpBench\\Extensions\\Sqlite\\SqliteExtension"
        ]
    }

Above you register the Sqlite extension and select it as the storage driver
for your project.

By default it will create an Sqlite database in `.phpbench.sqlite` in the
current working directory. It is recommended that you add this to your git
ignore file:

.. code-block:: bash

    # .gitignore
    # ...
    .phpbench.sqlite

For more details see the documentation :doc:`documentation <extensions/sqlite>`

.. warning::

    The Sqlite storage implementation is experimental and may change significantly or even
    be replaced (for example by a general SQL storage driver) in later versions.

Storing Results
---------------

In order to store benchmarking runs you simply need to give ``--store`` option
when running your benchmarks:

.. code-block:: bash

    $ phpbench run --store

Viewing the History
-------------------

Once you have stored some benchmark runs you can use the history command to
see what you have got:

.. code-block:: bash

    $ phpbench history
    Limit set to 10
    Run     Date                    VCS Branch      Context

    239     2016-02-10 12:10:35     storage
    238     2016-02-10 12:09:10     storage
    237     2016-02-10 12:08:54     storage
    236     2016-02-10 12:08:45     storage
    235     2016-02-10 12:08:01     storage

Querying and Report Generation
------------------------------

You can query the benchmarking history of the project and use any of the
existing :doc:`reports`:

.. code-block:: bash

    $ phpbench report --query='run: 239' --report=aggregate

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

Archiving
---------

Archiving provides a way to export and reimport data from and to the
configured storage. This allows you to:

- Backup your results (for example to a GIT repository).
- Migrate to other storage drivers.

By default PHPBench is configured to use an ``XML`` archiver, which will dump
results to a directory in the current working directory, ``_archive``.

.. note::

    In the future it will be possible to select GIT as an archiving
    mechanism, so that your benchmarks are always stored in your codes GIT
    repository.

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
