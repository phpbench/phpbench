PHPBench Reports
================

The Reports extension wraps a given storage driver (XML by default) and
additionally sends the results over HTTP to a `phpbench-reports`_ server.

PHPBench reports is a web application which indexes the results for
multiple projects in elastic search and provides visualization.

.. image:: https://user-images.githubusercontent.com/530801/34437867-586dafe4-eca2-11e7-8e63-c3ab6b1360f1.png

Installation
------------

Enable the `phpbench-reports`_ extension and set your storage driver to ``reports``:

.. code-block:: javascript

    {
       "extensions": {
           "PhpBench\\Extensions\\Reports\\ReportsExtension"
       }
    }

Configuration
-------------

Set the storage driver to ``reports``, you will need to configure both the URL
where `phpbench-reports`_ can be found, and the API key (which can be
requested from the PHPBench reports server):

.. code-block:: javascript

    {
        "storage": 'reports',
        "storage.reports.url" => "https://reports.phpbench.org",
        "storage.reports.api_key" => "<api-key>"
    }

.. note::

    The API key can also be provided via. an environment variable, see "travis
    integration" below.

By default it will wrap the XML storage driver, this can be changed as
follows:

.. code-block:: javascript

    {
        'storage.reports.inner_driver' => 'blah'
    }

Travis Integration
------------------

.. warning::

    Travis is heavily-loaded and quite unsuitable for benchmarking purposes,
    but it's potentially interesting.

You can use the `travis`_ CLI application to encrypt the API key env var:

.. code-block:: bash

    $ travis encrypt REPORTS_API_KEY=<your API key> --report=your/repo

You can then add the encrypted env var to `.travis.yml` as follows:

.. code-block:: yaml

    env:
        global:
            secure: "<encrypted key here>"

Configure PHPBench to run as normal with the `--store` option:

.. code-block:: yaml

    script:
        - ./vendor/bin/phpbench run --store

.. _travis: https://docs.travis-ci.com/user/encryption-keys/#Usage
.. _phpbench-reports: https://github.com/phpbench/phpbench-reports
