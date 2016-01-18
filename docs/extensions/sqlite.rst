Sqlite
======

The Sqlite extension provides a storage driver for sqlite. See the
:doc:`../storage` chapter for more information.

.. warning::

    The Sqlite storage implementation is experimental and may change significantly or even
    be replaced (for example by a general SQL storage driver) in later versions.

Installation
------------

The Sqlite extension is bundled with PHPBench, it just needs to be activated
in your ``phpbench.json`` configuration:

.. code-block:: javascript

    {
       "storage": "sqlite",
       "extensions": {
           "PhpBench\\Extensions\\Sqlite\\SqliteExtension"
       }
    }

In addition to adding the extension we also set ``sqlite`` as our storage
driver.

Configuration
-------------

You may configure the path of the Sqlite database as follows:

.. code-block:: javascript

    {
        ...
        "storage.sqlite.db_path": "/path/to/database.sqlite"
    }

.. note::

    Multiple projects cannot currently share the same sqlite database.
