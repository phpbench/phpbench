DBAL
====

The DBAL extension provides a storage driver for storing results to any
database supported by `doctrine dbal`_. By default it will use a file-based sqlite_
database which will be created in your current working directory and named
``.phpbench.sqlite``.

Currently it will only store basic metrics for each iteration, `time`,
`memory` (peak), `z-value`, `deviation` and the summary statistics.

.. warning::

    The DBAL extension does not provide an advanced storage capability and may
    be dropped from the core before the 1.0 release. In this case it will be
    available as an officially unsupported extension in a separate repository.

Installation
------------

The DBAL depends on the ``doctrine/dbal`` package. If you are using PHPBench
as a dependency of your project you will need to ensure that you have this
package installed, install it with composer::

    $ composer require --dev "doctrine/dbal"

You will then need to enable the extension (which is bundled with PHPBench):

.. code-block:: javascript

    {
       "storage": "dbal",
       "extensions": {
           "PhpBench\\Extensions\\Dbal\\DbalExtension"
       }
    }

In addition to adding the extension we also set ``sqlite`` as our storage
driver.

Configuration
-------------

You may configure the dbal with the ``storage.dbal.connection`` key in your
``phpbench.json`` file. For example, to change the path of the sqlite
database:

.. code-block:: javascript

    {
        ...
        "storage.dbal.connection": {
            "driver": "pdo_sqlite",
            "path": "/path/to/database"
        }
    }

Or to use another database driver, e.g. ``mysql``:

.. code-block:: javascript

    {
        ...
        "storage.dbal.connection": {
            "driver": "pdo_mysql",
            "dbname": "myproject_benchmarks",
            "user": "root",
        }
    }

If you are not using the ``pdo_sqlite`` driver, you will need to initialize
the database using the ``dbal:migrate`` command detailed below.

.. warning::

    Multiple projects cannot currently share the same sqlite database.

Migration
---------

The ``dbal:migrate`` command will update the database schema to the latest
version, if the database is "new" then it will create the schema.

.. important:: 

    Whilst it is normally safe to run this command, there is no guarantee that
    your data will be migrated properly. If you care about your data, then it
    is advisable to :ref:`archive <archive>` your data before migrating the database.

Running the command with no options will tell you how many operations need to
be executed on the database. To actually migrate you need to supply the
``--force`` option::

    $ phpbench dbal:migrate --force

You may also manually inspect the statements that will be executed using the
``--dump-sql`` option::

    $ phpbench dbal:migrate --dump-sql

.. _doctrine dbal: http://www.doctrine-project.org/projects/dbal.html
.. _sqlite: https://www.sqlite.org/
