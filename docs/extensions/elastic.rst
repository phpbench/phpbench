Elastic
=======

The Elastic search extrension provides a storage driver for `Elastic Search`_.

This is primarily useful for allowing you to analyze your benchmarking
results with a tool such as `Kibana`_.

.. note::

    This storage driver is a **decorator**, it will send the results to
    Elastic Search **in addition** to the decorated driver (the XML driver by
    default).

Installation
------------

The Elastic extension is bundled with PHPBench, it just needs to be activated:

.. code-block:: javascript

    {
       "extensions": [
           "PhpBench\\Extensions\\Elastic\\ElasticExtension"
       ]
    }

Configuration
-------------

.. code-block:: yaml

    storage.elastic.connection:
        scheme: http,
        host: localhost
        port: 9200
        index: phpbench,
    storage.elastic.inner_driver: xml
    storage.elastic.store_iterations: false

- ``storgae.elastic.connection``: Connection parameters and index name for elastic search.
- ``storage.elastic.inner_driver``: Wrap this driver.
- ``storage.elastic.store_iterations``: If each iteration result should be
  stored in addition to the aggregate results.
