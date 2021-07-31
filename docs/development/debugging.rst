Debugging
=========

Remote Script Templates
-----------------------

PHPBench renders a template to produce the benchmarking script and to gather
information about the benchmark execution environment. By default
this will be rendered in your system's temporary directory and subsequently
removed.

This makes it difficult to inspect the rendered template.

By adding the following configuration in your `phpbench.json` you can
render this template in a local directory and ensure that it is not deleted.

.. code-block:: json

    {
        "$schema":"./vendor/phpbench/phpbench/phpbench.schema.json",
        "runner.remote_script_path": ".phpbench/script",
        "runner.remote_script_remove": false
    }

Each remove will be rendered in this directory and can be executed with ``php
.phpbench/script/remote.template`` for example.
