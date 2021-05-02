Unless overridden with the ``--config`` option, PHPBench will try to load its
configuration from the current working directory. It will check for the
existence each of the files ``phpbench.json`` and ``phpbench.json.dist`` in
that order and use one if it exists.

.. code-block:: json

    {
        "runner.bootstrap": "vendor/autoload.php",
        "runner.path": "path/to/benchmarks",
        "report.outputs": {
             "my_csv_output": {
                 "extends": "delimited",
                 "delimiter": ",",
             }
        },
        "report.generators": {
            "my_report": {
                "extends": "aggregate"
            }
        }
    }
