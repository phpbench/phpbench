Unless overridden with the ``--config`` option, PHPBench will try to load its
configuration from the current working directory. It will check for the
existence each of the files ``phpbench.json`` and ``phpbench.json.dist`` in
that order and use one if it exists.

.. code-block:: json

    {
        "bootstrap": "vendor/autoload.php",
        "path": "path/to/benchmarks",
        "outputs": {
             "my_output": {
                 "extends": "html",
                 "file": "my_report.html",
                 "title": "Hello World"
             }
        },
        "reports": {
            "my_report": {
                "extends": "aggregate"
            }
        }
    }

.. note::

    Typically you should use ``phpbench.json.dist`` in your projects. This
    allows the end-user of your library to override your configuration by creating
    ``phpbench.json``.
