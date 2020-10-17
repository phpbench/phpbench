Debugging
=========

Rendered Templates
------------------

PHPBench renders a template to produce the benchmarking script, but default
this will be rendered in your system's temporary directory and subsequently
removed.

This makes it difficult to inspect the rendered template.

By adding the following executor configuration in your `phpbench.json` you can
render this template in a local directory and ensure that it is not deleted.

.. code-block:: json

    {
        "executors": {
            "keepscript": {
                "executor": "microtime",
                "render_path": ".phpbench/script.php",
                "remove_script": false
            }
        }
    }


You can then run PHPBench with the ``--executor=keepscript`` option.
